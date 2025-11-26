<?php

namespace App\Classes;

use App\Models\Lection;
use App\Models\Eastersunday;
use Carbon\Carbon;

class LectionaryService
{
    protected Carbon $date;
    protected string $cycleLetter; // A|B|C

    public function __construct($date = null)
    {
        $this->date = $date ? Carbon::parse($date) : Carbon::today();
        $this->cycleLetter = $this->determineCycleLetter($this->date);
    }

    /**
     * Public API: get readings for a given date
     *
     * @param string|Carbon|null $date
     * @return array
     */
    public function getReadings($date = null): array
    {
        $this->date = $date ? Carbon::parse($date) : $this->date;

        // Determine the Sunday for this date (the Sunday of the week; if date is Sunday it is itself)
        $sunday = $this->getSundayForDate($this->date);

        // Build mappings and names
        $sundayName = $this->getSundayName($sunday);
        $sundayReadings = $this->fetchReadingsFor($sundayName);

        // Midweek: all special weekday services that fall in this week (Mon-Sat before that Sunday)
        $midweek = $this->getMidweekServicesForWeek($sunday);

        return [
            'sunday' => [
                'name' => $sundayName,
                'readings' => $sundayReadings,
            ],
            'midweek' => $midweek,
        ];
    }

    /* -----------------------
       Core helpers
       ----------------------- */

    protected function getSundayForDate(Carbon $date): Carbon
    {
        // Sunday is considered the Sunday of the week. If date is Sunday return it,
        // otherwise return the *next* Sunday (so week Mon-Sun, and our midweek is Mon-Sat).
        return $date->isSunday() ? $date->copy() : $date->copy()->next(Carbon::SUNDAY);
    }

    protected function fetchReadingsFor(?string $name): ?array
    {
        if (!$name) {
            return null;
        }

        $lection = Lection::where('lection', $name)
            ->where('year', $this->cycleLetter)
            ->first();

        if (!$lection) {
            return null;
        }

        return [
            'ot' => $lection->ot ?? null,
            'psalm' => $lection->psalm ?? null,
            'nt' => $lection->nt ?? null,
            'gospel' => $lection->gospel ?? null,
        ];
    }

    /* -----------------------
       Special & midweek services
       ----------------------- */

    protected function getMidweekServicesForWeek(Carbon $sunday): array
    {
        // Week for midweek runs Monday -> Saturday before the Sunday
        $weekStart = $sunday->copy()->subDays(6); // Monday
        $weekEnd = $sunday->copy()->subDay(); // Saturday

        $services = [];

        // Epiphany (Jan 6) - considered a special day; include if it falls in this week
        $epiphany = Carbon::create($sunday->year, 1, 6);
        if ($this->dateInRange($epiphany, $weekStart, $weekEnd)) {
            $r = $this->fetchReadingsFor('Epiphany');
            if ($r) $services[] = ['name' => 'Epiphany', 'readings' => $r];
        }

        // Ash Wednesday (46 days before Easter)
        $ash = $this->getEasterSunday($sunday->year)->copy()->subDays(46);
        if ($this->dateInRange($ash, $weekStart, $weekEnd)) {
            $r = $this->fetchReadingsFor('Ash Wednesday');
            if ($r) $services[] = ['name' => 'Ash Wednesday', 'readings' => $r];
        }

        // Holy Week (Mon - Sat before Easter)
        $easter = $this->getEasterSunday($sunday->year);
        $holy = [
            'Monday of Holy Week'    => $easter->copy()->subDays(6),
            'Tuesday of Holy Week'   => $easter->copy()->subDays(5),
            'Wednesday of Holy Week' => $easter->copy()->subDays(4),
            'Holy Thursday'          => $easter->copy()->subDays(3),
            'Good Friday'            => $easter->copy()->subDays(2),
            'Holy Saturday'          => $easter->copy()->subDays(1),
        ];
        foreach ($holy as $label => $d) {
            if ($this->dateInRange($d, $weekStart, $weekEnd)) {
                $r = $this->fetchReadingsFor($label);
                if ($r) $services[] = ['name' => $label, 'readings' => $r];
            }
        }

        // Ascension Day (Thursday, 39 days after Easter)
        $ascension = $easter->copy()->addDays(39);
        if ($this->dateInRange($ascension, $weekStart, $weekEnd)) {
            $r = $this->fetchReadingsFor('Ascension of the Lord') ?: $this->fetchReadingsFor('Ascension Day');
            if ($r) $services[] = ['name' => 'Ascension Day', 'readings' => $r];
        }

        // Christmas Eve (Dec 24) may fall in a week that spans Advent -> Christmas
        $christmasEve = Carbon::create($sunday->year, 12, 24);
        if ($this->dateInRange($christmasEve, $weekStart, $weekEnd)) {
            $r = $this->fetchReadingsFor('Christmas Eve');
            if ($r) $services[] = ['name' => 'Christmas Eve', 'readings' => $r];
        }

        // Sort by date ascending to keep chronological order
        usort($services, function ($a, $b) {
            // both will exist in DB; use OT field as stable fallback if needed (not ideal),
            // but better to attempt matching by fetching Lection date - we don't have dates in DB.
            // For now preserve the inserted order; midweek array will be chronological because we checked known dates in order.
            return 0;
        });

        return $services;
    }

    protected function dateInRange(Carbon $d, Carbon $start, Carbon $end): bool
    {
        return $d->between($start->startOfDay(), $end->endOfDay());
    }

    /* -----------------------
       Sunday naming logic (RCL, System A)
       ----------------------- */

    protected function getSundayName(Carbon $sunday): ?string
    {
        // Standardize input to Sunday date (should already be)
        $sunday = $sunday->copy()->startOfDay();

        // 1) Advent Sundays (4 weeks before Christmas) - check first because Advent spans year-end
        $adventStart = $this->getAdventStartForLiturgicalYear($sunday->year);
        $christmas = Carbon::create($sunday->year, 12, 25);

        if ($sunday->betweenIncluded($adventStart, $christmas->copy()->subDay())) {
            // week offset from adventStart -> 0..3
            $offset = $adventStart->diffInWeeks($sunday);
            $labels = ['Advent 1','Advent 2','Advent 3','Advent 4'];
            return $labels[$offset] ?? 'Advent';
        }

        // 2) Christmas & Sundays after Christmas
        // First Sunday after Christmas: the first Sunday between Dec 26 and Jan 1 inclusive
        $firstAfterChristmas = $christmas->copy()->addDay()->next(Carbon::SUNDAY);
        // But compute robustly: find the first Sunday on or after Dec 26
        $first = Carbon::create($sunday->year, 12, 26)->copy()->nextOrEqual(Carbon::SUNDAY);
        if ($sunday->isSameDay($first)) {
            return 'First Sunday after Christmas Day';
        }

        // Second Sunday after Christmas: if another Sunday before Jan 6
        $second = $first->copy()->addWeek();
        if ($sunday->isSameDay($second) && $sunday->lt(Carbon::create($sunday->year + 1, 1, 6))) {
            return 'Second Sunday after Christmas Day';
        }

        // 3) Epiphany and Sundays after Epiphany up to Transfiguration (last Sun before Lent)
        $epiphany = Carbon::create($sunday->year, 1, 6);
        // First Sunday on or after Jan 6
        $firstEpiphanySunday = $epiphany->copy()->nextOrEqual(Carbon::SUNDAY);
        // Determine Ash Wednesday for this calendar year
        $ashWednesday = $this->getEasterSunday($sunday->year)->copy()->subDays(46);
        // Transfiguration = last Sunday before Ash Wednesday
        $transfigurationSunday = $ashWednesday->copy()->previous(Carbon::SUNDAY);

        // If the Sunday is Jan 6 itself and it is Sunday -> Epiphany
        if ($sunday->isSameDay($epiphany) && $epiphany->isSunday()) {
            return 'Epiphany';
        }

        // Baptism of the Lord: the Sunday after Epiphany (if Jan 6 is Sunday, then next Sunday; otherwise it's the first Sunday on/after Jan 7)
        $baptismSunday = $epiphany->isSunday() ? $epiphany->copy()->addWeek() : $firstEpiphanySunday;
        if ($sunday->isSameDay($baptismSunday)) {
            return 'Baptism of the Lord';
        }

        // Epiphany Sundays (numbered) until Transfiguration (Transfiguration replaces last Epiphany Sunday)
        if ($sunday->gte($baptismSunday) && $sunday->lte($transfigurationSunday)) {
            if ($sunday->isSameDay($transfigurationSunday)) {
                return 'Transfiguration';
            }
            // Week index: 1 for the Sunday after Baptism, etc.
            $weeks = $baptismSunday->diffInWeeks($sunday) + 1; // start at 1
            return "Epiphany {$weeks}";
        }

        // 4) Lent and Holy Week handled by midweek; Sundays in Lent:
        // Ash Wednesday -> Lent Sunday start = the following Sunday after Ash Wed
        $lentFirstSunday = $ashWednesday->copy()->next(Carbon::SUNDAY);
        if ($sunday->gte($lentFirstSunday) && $sunday->lt($this->getEasterSunday($sunday->year))) {
            $weeks = $lentFirstSunday->diffInWeeks($sunday) + 1;
            // If it's the week of Palm Sunday (last week) we may label 'Palm Sunday' when appropriate
            $palmSunday = $this->getEasterSunday($sunday->year)->copy()->subWeek();
            if ($sunday->isSameDay($palmSunday)) {
                return 'Palm Sunday';
            }
            return "Lent {$weeks}";
        }

        // 5) Easter season (Easter Sunday ... Pentecost)
        $easter = $this->getEasterSunday($sunday->year);
        if ($sunday->isSameDay($easter)) {
            return 'Easter Sunday';
        }
        $pentecost = $easter->copy()->addDays(49); // 7 weeks after Easter -> Pentecost
        if ($sunday->gt($easter) && $sunday->lt($pentecost)) {
            // count Easter Sundays: 2..7 (Easter week handled above)
            $weeksAfterEaster = $easter->diffInWeeks($sunday) + 1;
            return "Easter {$weeksAfterEaster}";
        }
        if ($sunday->isSameDay($pentecost)) {
            return 'Pentecost';
        }

        // 6) Trinity Sunday (first Sunday after Pentecost)
        $trinity = $pentecost->copy()->addWeek();
        if ($sunday->isSameDay($trinity)) {
            return 'Trinity Sunday';
        }

        // 7) Ordinary Sundays AFTER Trinity (RCL): first ordinary is the Sunday AFTER Trinity => Ordinary 8
        $firstOrdinary = $trinity->copy()->addWeek(); // first Sunday to be Ordinary 8
        $adventStart = $this->getAdventStartForLiturgicalYear($sunday->year);

        if ($sunday->gte($firstOrdinary) && $sunday->lt($adventStart)) {
            // Build mapping deterministically:
            // iterate Sundays from firstOrdinary up to but not including adventStart;
            // assign Ordinary 8,9,... sequentially
            $map = [];
            $dt = $firstOrdinary->copy();
            $num = 8;
            while ($dt->lt($adventStart)) {
                $map[$dt->toDateString()] = $num;
                $dt->addWeek();
                $num++;
            }

            $key = $sunday->toDateString();
            if (isset($map[$key])) {
                return 'Ordinary ' . $map[$key];
            }
        }

        // If none matched, return null (caller can handle)
        return null;
    }

    /* -----------------------
       Utility & date calculations
       ----------------------- */

    protected function getEasterSunday(int $year): Carbon
    {
        // Try DB first (your eastersundays table)
        $row = Eastersunday::where('year', $year)->first();
        if ($row) {
            return Carbon::parse($row->eastersunday);
        }

        // Fallback: compute via PHP easter_date
        $ts = easter_date($year); // returns timestamp midnight GMT on Easter Sunday
        return Carbon::createFromTimestamp($ts)->startOfDay();
    }

    /**
     * Determine the first Sunday of Advent for a calendar year.
     * Advent 1 is the Sunday between Nov 27 and Dec 3 (inclusive).
     */
    protected function getAdventStartForLiturgicalYear(int $calendarYear): Carbon
    {
        // Find the Sunday between Nov 27 and Dec 3 inclusive
        $start = Carbon::create($calendarYear, 11, 27);
        for ($i = 0; $i <= 6; $i++) {
            $d = $start->copy()->addDays($i);
            if ($d->isSunday()) {
                return $d;
            }
        }

        // Should never happen, but fallback:
        return Carbon::create($calendarYear, 11, 27)->next(Carbon::SUNDAY);
    }

    /**
     * Determine liturgical cycle letter (A/B/C) for a date.
     * RCL Year A started at Advent of 2007 (then 2010, 2013, 2016, 2019, ...)
     */
    protected function determineCycleLetter(Carbon $date): string
    {
        // Advent 1 for the current calendar year
        $adventStart = $this->getAdventStartForLiturgicalYear($date->year);

        // If date >= Advent start, it's the **next liturgical year**
        $liturgicalYear = $date->gte($adventStart) ? $date->year + 1 : $date->year;

        // Cycle mapping (Year A starting at Advent 2007)
        $baseA = 2007; 
        $index = ($liturgicalYear - $baseA) % 3;
        if ($index < 0) $index += 3;
        $map = ['A', 'B', 'C'];
        return $map[$index];
    }

}
