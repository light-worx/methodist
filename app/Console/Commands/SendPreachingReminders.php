<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lightworx\FilamentPwa\Facades\PushNotification;

class SendPreachingReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'methodist:preaching-reminders
                            {--dry-run : Log what would be sent without dispatching any notifications}
                            {--days=7  : How many days ahead to look for scheduled services (default: 7)}';

    /**
     * The console command description.
     */
    protected $description = 'Send preaching-reminder push notifications to ministers and preachers '
                           . 'scheduled within the next N days, including RCL lectionary readings.';

    private const LECTIONARY_API = 'https://lectionary.lightworx.co.za/api/index.php';

    // =========================================================================

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $days   = (int)  $this->option('days');
        $today  = Carbon::today();
        $until  = Carbon::today()->addDays($days)->endOfDay();

        if ($dryRun) {
            $this->warn('[DRY RUN] No notifications will be dispatched.');
        }

        $this->info(
            "Looking for preachers/ministers scheduled between "
            . "{$today->toDateString()} and {$until->toDateString()}…"
        );

        // ------------------------------------------------------------------
        // 1.  Fetch all qualifying plan rows.
        //
        //     Join chain:
        //       plans.person_id
        //         → persons.id
        //         → persons.phone
        //           → user_preferences.phone      (no person_id on that table)
        //             → push_subscriptions.user_preference_id  (device-based, not user-based)
        //
        //     Filters applied:
        //       • person is an active preacher OR active minister
        //       • user_preferences.custom_settings->preaching_reminders is true
        //       • at least one push_subscription exists for that preference
        //       • plans.servicedate falls within the look-ahead window
        //       • person is not soft-deleted
        // ------------------------------------------------------------------
        $rows = DB::table('plans as pl')
            ->join('services as svc', 'svc.id', '=', 'pl.service_id')
            ->join('societies as soc', 'soc.id', '=', 'svc.society_id')
            ->join('persons as p', 'p.id', '=', 'pl.person_id')
            ->join('user_preferences as up', 'up.phone', '=', 'p.phone')
            // opted in to preaching reminders
            ->where(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(up.custom_settings, '$.preaching_reminders'))"),
                'true'
            )
            // has at least one registered device
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('push_subscriptions as ps')
                  ->whereColumn('ps.user_preference_id', 'up.id');
            })
            // is a preacher or a minister (either role qualifies)
            ->where(function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('preachers')
                        ->whereColumn('preachers.person_id', 'p.id')
                        ->whereNull('preachers.deleted_at');
                })->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('ministers')
                        ->whereColumn('ministers.person_id', 'p.id')
                        ->whereNull('ministers.deleted_at');
                });
            })
            ->whereBetween('pl.servicedate', [$today->toDateString(), $until->toDateString()])
            ->whereNotNull('pl.servicedate')
            ->whereNull('p.deleted_at')
            ->select([
                'p.id          as person_id',
                'p.title',
                'p.firstname',
                'p.surname',
                'p.phone',
                'pl.servicedate',
                'svc.servicetime',
                'soc.society   as society_name',
            ])
            ->orderBy('p.id')
            ->orderBy('pl.servicedate')
            ->get();

        if ($rows->isEmpty()) {
            $this->info('No scheduled preachers found in the window. Nothing to send.');
            return self::SUCCESS;
        }

        $uniquePreachers = $rows->pluck('person_id')->unique()->count();
        $this->info("Found {$rows->count()} plan row(s) across {$uniquePreachers} preacher(s).");

        // ------------------------------------------------------------------
        // 2.  Pre-fetch lectionary readings for every unique service date.
        //     One HTTP call per unique date — avoids hammering the API.
        // ------------------------------------------------------------------
        $uniqueDates  = $rows->pluck('servicedate')->unique()->values()->all();
        $lectionaries = $this->fetchLectionaryReadings($uniqueDates);

        // ------------------------------------------------------------------
        // 3.  Group by person, build the message body, and send.
        //     We use PushNotification::toPhone() because we already have the
        //     phone number from the persons table — no need to load a model.
        // ------------------------------------------------------------------
        $sent   = 0;
        $failed = 0;

        foreach ($rows->groupBy('person_id') as $personId => $personRows) {
            $first = $personRows->first();
            $name  = trim("{$first->firstname} {$first->surname}");
            $phone = $first->phone;

            $engagements = $personRows->map(fn ($row) => [
                'date'      => $row->servicedate,
                'time'      => $row->servicetime,
                'society'   => $row->society_name,
                'title'     => $first->title,
                'firstname' => $first->firstname,
                'surname'   => $first->surname,
                'reading'   => $this->findReading($row->servicedate, $lectionaries),
            ])->all();

            $count = count($engagements);
            $title = $count === 1
                ? '📖 Preaching Reminder'
                : "📖 Preaching Reminders ({$count} services)";
            $body  = $this->buildBody($engagements);

            if ($dryRun) {
                $this->line("[DRY RUN] Would notify {$name} ({$phone}) — {$count} service(s):");
                foreach ($engagements as $e) {
                    $tag = $e['reading'] ? "[{$e['reading']['service']}]" : '[no reading found]';
                    $this->line("  • {$e['society']} on {$e['date']} {$tag}");
                }
                $this->line("  Title : {$title}");
                $this->line("  Body  :\n" . collect(explode("\n", $body))->map(fn ($l) => "    {$l}")->implode("\n"));
                $sent++;
                continue;
            }

            try {
                $result = PushNotification::toPhone(
                    phone: $phone,
                    title: $title,
                    body:  $body,
                    url:   '/dashboard',
                );

                if ($result->noDevices) {
                    // Preference exists but all subscriptions have lapsed — warn and move on
                    $this->warn("  ⚠  No active devices for {$name} ({$phone}) — skipping.");
                } elseif ($result->failed > 0) {
                    $this->warn("  ⚠  {$name}: sent={$result->sent}, failed={$result->failed}.");
                    $failed += $result->failed;
                    $sent   += $result->sent;
                } else {
                    $this->line("  ✓  Notified {$name} on {$result->sent} device(s).");
                    $sent += $result->sent;
                }

            } catch (\Throwable $e) {
                $this->error("  ✗  Exception for {$name}: {$e->getMessage()}");
                Log::error('SendPreachingReminders: push error', [
                    'person_id' => $personId,
                    'phone'     => $phone,
                    'error'     => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                ]);
                $failed++;
            }
        }

        $this->info("Done. Devices notified: {$sent}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    // =========================================================================
    // Lectionary helpers
    // =========================================================================

    /**
     * Fetch RCL readings for an array of date strings.
     *
     * API response shape:
     *   { "sunday": { "YYYY-MM-DD": { service, rcl, ot, psalm, nt, gospel } } }
     *
     * Returns an array keyed by the Sunday date string the API reports.
     */
    private function fetchLectionaryReadings(array $dates): array
    {
        $readings = [];

        foreach (array_unique($dates) as $date) {
            try {
                $response = Http::timeout(8)->get(self::LECTIONARY_API, ['date' => $date]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['sunday']) && is_array($data['sunday'])) {
                        foreach ($data['sunday'] as $sundayDate => $entry) {
                            $readings[$sundayDate] = $entry;
                        }
                    }
                } else {
                    $this->warn("  Lectionary API returned HTTP {$response->status()} for {$date}.");
                }
            } catch (\Throwable $e) {
                $this->warn("  Lectionary fetch failed for {$date}: {$e->getMessage()}");
                Log::warning('SendPreachingReminders: lectionary error', [
                    'date'  => $date,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $readings;
    }

    /**
     * Return the best-matching lectionary entry for a given preaching date.
     * Exact match (service falls on a Sunday) first; otherwise nearest Sunday.
     */
    private function findReading(string $date, array $lectionaries): ?array
    {
        if (isset($lectionaries[$date])) {
            return $lectionaries[$date];
        }

        $ts   = strtotime($date);
        $best = null;
        $diff = PHP_INT_MAX;

        foreach ($lectionaries as $sundayDate => $entry) {
            $d = abs($ts - strtotime($sundayDate));
            if ($d < $diff) {
                $diff = $d;
                $best = $entry;
            }
        }

        return $best;
    }

    // =========================================================================
    // Notification body builder
    // =========================================================================

    /**
     * Build the push notification body.
     *
     * Structure:
     *   Preaching reminder for: [title] [firstname] [surname]
     *
     *   [Date 1]
     *   📍 Society A — 9:00 AM
     *   📍 Society B — 11:00 AM    ← multiple services on the same date
     *   📜 [Service name] (RCL Year A)
     *      OT / Psalm / NT / Gospel
     *
     *   [Date 2]
     *   📍 Society C — 6:00 PM
     *   📜 ...
     *
     * Readings appear once per unique date, not once per service.
     *
     * @param  array[]  $engagements
     */
    private function buildBody(array $engagements): string
    {
        $first = $engagements[0];

        // Build the salutation line, omitting the title when it is empty
        $titlePart = trim($first['title'] ?? '');
        $nameParts = array_filter([$titlePart, $first['firstname'] ?? '', $first['surname'] ?? '']);
        $fullName  = implode(' ', $nameParts);

        $lines   = [];
        $lines[] = "Preaching reminder for: {$fullName}";
        $lines[] = '';

        // Group services by date so each date block lists all its services
        // before showing a single set of lectionary readings.
        $byDate = collect($engagements)->groupBy('date');

        foreach ($byDate as $date => $dateEngagements) {
            $lines[] = Carbon::parse($date)->format('l, j F Y');

            // One line per service on this date
            foreach ($dateEngagements as $e) {
                $time    = isset($e['time'])
                    ? Carbon::parse($e['time'])->format('g:i A')
                    : 'Time TBC';
                $lines[] = "📍 {$e['society']} — {$time}";
            }

            // One set of lectionary readings for this date
            $reading = $dateEngagements->first()['reading'];

            if (! empty($reading)) {
                $lines[] = "📜 {$reading['service']} (RCL Year {$reading['rcl']})";
                $lines[] = "   OT: {$reading['ot']}";
                $lines[] = "   Psalm: {$reading['psalm']}";
                $lines[] = "   NT: {$reading['nt']}";
                $lines[] = "   Gospel: {$reading['gospel']}";
            } else {
                $lines[] = "   (Lectionary readings unavailable for this date)";
            }

            $lines[] = ''; // blank line between date blocks
        }

        return trim(implode("\n", $lines));
    }
}