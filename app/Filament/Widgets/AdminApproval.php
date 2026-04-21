<?php

namespace App\Filament\Widgets;

use App\Models\Idea;
use App\Models\Society;
use Filament\Widgets\Widget;

class AdminApproval extends Widget
{
    protected string $view = 'filament.widgets.admin-approval';

    public function getData(): array
    {
        $locations = Society::with('circuit.district')->where('unverified_location',true)->orderBy('society')->get();
        $ideas = Idea::where('published','<>',1)->orderBy('created_at','desc')->get();
        return [
            'societies' => $locations ?? [],
            'ideas' => $ideas ?? []
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}