<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PreachingPlan extends Page
{
    protected string $view = 'preaching-plan';
    public $record;
    public $today;
    protected static string $layout = 'components.layouts.no-sidebar';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->record = request()->query('record');
        $this->today = request()->query('today') ?? date('Y-m-d');
    }
}
