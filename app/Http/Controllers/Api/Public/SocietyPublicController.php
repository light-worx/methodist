<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\Circuit;
use App\Models\Midweek;
use App\Models\Plan;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SocietyPublicController extends Controller
{
    /**
     * GET /api/public/societies/{societySlug}/midweeks/{year}
     *
     * Returns all midweek service dates and times for a given society and calendar year.
     *
     * Midweek service definitions are stored as JSON on the circuit (circuits.midweeks).
     * Each entry in that JSON array is expected to have at least:
     *   { "day": "Wednesday", "time": "19:00", "type": "Midweek Service" }
     *
     * The circuit also stores plan_month: the month (1–12) in which the plan period starts,
     * allowing us to generate the correct set of dates for the requested calendar year.
     *
     * @param  string  $id  The ID of the society (from societies.id)
     * @param  int     $year         The calendar year, e.g. 2025
     */
    public function midweeks(string $id, int $year): JsonResponse
    {
        // Validate year is sensible
        if ($year < 2000 || $year > 2100) {
            return response()->json(['error' => 'Invalid year.'], 422);
        }

        // Resolve the society and eagerly load its circuit
        $society = Society::with('circuit')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $society) {
            return response()->json(['error' => 'Society not found.'], 404);
        }

        $circuit = $society->circuit;

        if (! $circuit || ! $circuit->active) {
            return response()->json(['error' => 'Circuit not found or inactive.'], 404);
        }

        // midweeks is a JSON array of midweek service definitions on the circuit
        $midweekDefinitions = $circuit->midweeks ?? [];

        if (empty($midweekDefinitions)) {
            return response()->json([
                'society'  => $society->society,
                'circuit'  => $circuit->circuit,
                'year'     => $year,
                'midweeks' => [],
            ]);
        }

        $occurrences = $this->calculate_midweeks(
            Carbon::parse("$year-01-01")->startOfDay(),
            Carbon::parse("$year-12-31")->endOfDay(), 
            $midweekDefinitions
        );

        return response()->json([
            'society'  => $society->society,
            'circuit'  => $circuit->circuit,
            'year'     => $year,
            'midweeks' => $occurrences,
        ]);
    }

    /**
     * GET /api/public/societies/{societySlug}/preachers/{year}
     *
     * Returns all planned preaching appointments for a society in a given calendar year.
     * Results include the preacher's full name, the service date, and the service time,
     * ordered by date then service time.
     *
     * Relationship chain:
     *   societies → services (society_id)
     *              → plans   (service_id)  filtered by servicedate year
     *              → persons (person_id)   for the preacher's name
     *
     * @param  string  $id           The id of the society (from societies.id)
     * @param  int     $year         The calendar year, e.g. 2025
     */
    public function preachers(string $id, int $year): JsonResponse
    {
        // Validate year
        if ($year < 2000 || $year > 2100) {
            return response()->json(['error' => 'Invalid year.'], 422);
        }

        $society = Society::where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $society) {
            return response()->json(['error' => 'Society not found.'], 404);
        }

        /*
         * Join chain: services → plans → persons
         *
         * We query plans joined to services (to get the service time) and persons
         * (to get the preacher name), filtered to this society and year.
         *
         * plans.service_id  → services.id
         * plans.person_id   → persons.id
         * services.society_id = $society->id
         * YEAR(plans.servicedate) = $year
         * plans.person_id IS NOT NULL  (unassigned slots are excluded)
         */
        $appointments = Plan::query()
            ->join('services', 'plans.service_id', '=', 'services.id')
            ->join('persons',  'plans.person_id',  '=', 'persons.id')
            ->where('services.society_id', $society->id)
            ->whereYear('plans.servicedate', $year)
            ->whereNotNull('plans.person_id')
            ->orderBy('plans.servicedate')
            ->orderBy('services.servicetime')
            ->select([
                'plans.servicedate',
                'services.servicetime',
                'plans.servicetype',
                'persons.title',
                'persons.firstname',
                'persons.surname',
            ])
            ->get()
            ->map(function ($row) {
                $name = trim(
                    collect([$row->firstname, $row->surname])
                        ->filter()
                        ->implode(' ')
                );

                return [
                    'date'         => $row->servicedate,
                    'day'          => Carbon::parse($row->servicedate)->format('l'),
                    'time'         => $row->servicetime,
                    'service_type' => $row->servicetype,
                    'preacher'     => $name,
                ];
            });

        return response()->json([
            'society'      => $society->society,
            'year'         => $year,
            'appointments' => $appointments,
        ]);
    }

    private function calculate_midweeks($start,$end, $circuitmws){
        $years=array();
        $years[]=date('Y',strtotime($start));
        $years[]=date('Y',strtotime($end));
        array_unique($years);
        $mws = Midweek::whereIn('midweek',$circuitmws)->get();
        $dates = array();
        foreach ($mws as $mw){
            if ($mw->type=="fixed"){
                foreach ($years as $yr){
                    $temp=date('Y-m-d',strtotime($yr . '-' . $mw->month . '-' . $mw->day));
                    if (($temp>=$start) and ($temp<=$end) and (date('w',strtotime($temp)>0) and (!in_array($temp,$dates)))){
                        $dates[$mw->midweek]=$temp;
                    }
                }
            } else {
                foreach ($years as $yr){
                    $easter = DB::table('eastersundays')
                        ->whereYear('eastersunday', $yr)
                        ->value('eastersunday');
                    $temp=Carbon::parse($easter)->addDays($mw->offset)->format('Y-m-d');
                    if (($temp>=$start) and ($temp<=$end) and (!in_array($temp,$dates))){
                        $dates[$mw->midweek]=$temp;
                    }
                }
            }
        }
        asort($dates);
        return $dates;
    }
}