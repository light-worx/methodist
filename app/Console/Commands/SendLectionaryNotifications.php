<?php

namespace App\Console\Commands;

use App\Models\UserPreference;
use App\Services\NotificationDispatcher;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SendLectionaryNotifications extends Command
{
    protected $signature   = 'notify:lectionary {--date= : Override the target date (Y-m-d)}';
    protected $description = 'Send lectionary readings to preachers scheduled for the coming Sunday';

    public function __construct(private readonly NotificationDispatcher $dispatcher)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : $this->nextSunday();

        $this->info("Fetching lectionary for {$targetDate->toDateString()}…");

        // ── 1. Fetch lectionary from external API ──────────────────
        $lectionary = $this->fetchLectionary($targetDate);

        if (! $lectionary) {
            $this->error('Could not fetch lectionary data.');
            return self::FAILURE;
        }

        // ── 2. Find preachers scheduled for that date ──────────────
        // Join: plans → services → societies, persons → user_preferences
        // Group by person so multiple services collapse into one notification
        $preachers = $this->resolvePreachers($targetDate);

        if ($preachers->isEmpty()) {
            $this->info('No preachers with push subscriptions found for this date.');
            return self::SUCCESS;
        }

        $this->info("Sending to {$preachers->count()} preacher(s)…");

        // ── 3. Dispatch one personalised notification per preacher ──
        foreach ($preachers as $preacher) {
            $pref = UserPreference::find($preacher->user_preference_id);
            if (! $pref) {
                continue;
            }

            $body = $this->buildBody($lectionary, $preacher->services_summary);

            $this->dispatcher->lectionary(
                title:      $lectionary['service'],
                body:       $body,
                recipients: collect([$pref]),
                url:        '/lectionary',
            );
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function nextSunday(): Carbon
    {
        $now = Carbon::now();
        return $now->dayOfWeek === Carbon::SUNDAY ? $now : $now->next(Carbon::SUNDAY);
    }

    private function fetchLectionary(Carbon $date): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://lectionary.lightworx.co.za/api/index.php');

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();

            // The API returns the current/upcoming Sunday — take the first entry
            $sundays = $data['sunday'] ?? [];
            $dateKey = $date->toDateString();

            // Use exact date if present, otherwise fall back to first entry
            return $sundays[$dateKey] ?? array_values($sundays)[0] ?? null;

        } catch (\Throwable $e) {
            $this->error("Lectionary API error: {$e->getMessage()}");
            return null;
        }
    }

    private function resolvePreachers(Carbon $date): Collection
    {
        // Normalise phone → E.164: strip leading 0, prepend +27
        // user_preferences.mobile is already E.164 (+27...)
        // persons.phone may have a leading 0 — we normalise in SQL
        return DB::table('plans')
            ->join('services', 'services.id', '=', 'plans.service_id')
            ->join('societies', 'societies.id', '=', 'services.society_id')
            ->join('persons', 'persons.id', '=', 'plans.person_id')
            ->join('user_preferences', function ($join) {
                // Match on E.164 mobile in user_preferences vs phone in persons.
                // Normalise persons.phone: if starts with 0, replace with +27
                $join->on(
                    'user_preferences.mobile', '=',
                    DB::raw("CASE
                        WHEN persons.phone LIKE '0%'
                        THEN CONCAT('+27', SUBSTRING(persons.phone, 2))
                        WHEN persons.phone LIKE '+%'
                        THEN persons.phone
                        ELSE CONCAT('+', persons.phone)
                    END")
                );
            })
            ->where('plans.servicedate', $date->toDateString())
            ->where('user_preferences.notif_lectionary', true)
            ->whereNotNull('user_preferences.push_endpoint')
            ->whereNotNull('user_preferences.push_keys')
            ->select([
                'user_preferences.id as user_preference_id',
                'persons.id as person_id',
                DB::raw("
                    GROUP_CONCAT(
                        CONCAT(
                            DATE_FORMAT(services.servicetime, '%H:%i'),
                            ' ',
                            societies.name,
                            CASE
                                WHEN plans.servicetype IS NOT NULL AND plans.servicetype != ''
                                THEN CONCAT(' (', plans.servicetype, ')')
                                ELSE ''
                            END
                        )
                        ORDER BY services.servicetime ASC
                        SEPARATOR ' · '
                    ) as services_summary
                "),
            ])
            ->groupBy('user_preferences.id', 'persons.id')
            ->get();
    }

    private function buildBody(array $lectionary, string $servicesSummary): string
    {
        $lines = [];

        $lines[] = "You are preaching at: {$servicesSummary}";
        $lines[] = '';
        $lines[] = "OT: {$lectionary['ot']}";
        $lines[] = "Ps: {$lectionary['psalm']}";
        $lines[] = "NT: {$lectionary['nt']}";
        $lines[] = "Gospel: {$lectionary['gospel']}";

        return implode("\n", $lines);
    }
}