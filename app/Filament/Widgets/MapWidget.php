<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class MapWidget extends Widget
{
    protected string $view = 'filament.widgets.map-widget';

    protected static bool $isDiscovered = true;

    public static function canView(): bool
    {
        return true;
    }
}
