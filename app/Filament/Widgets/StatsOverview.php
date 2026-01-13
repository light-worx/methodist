<?php

namespace App\Filament\Widgets;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Minister;
use App\Models\Preacher;
use App\Models\Society;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Districts', District::where('active',1)->count() . '/' . District::count()),
            Stat::make('Circuits', Circuit::where('active',1)->count() . '/' . Circuit::count()),
            Stat::make('Societies', Society::count()),
            Stat::make('Ministers', Minister::count()),
            Stat::make('Preachers', Preacher::count()),
        ];
    }
}
