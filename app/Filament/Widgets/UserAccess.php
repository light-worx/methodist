<?php

namespace App\Filament\Widgets;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UserAccess extends Widget
{
    protected string $view = 'filament.widgets.user-access';
    
    protected static ?int $sort = -11;

    public function getData(): array
    {
        $user = Auth::user();
        
        if ($user->hasRole('super_admin')) {
            return [
                'user_name' => $user->name,
                'is_super_admin' => true,
                'districts' => [],
                'circuits' => [],
                'societies' => [],
            ];
        }

        $dis=District::whereIn('id',$user->districts)->orderBy('district')->get();
        foreach ($dis as $dd){
            $districts[]="<a href=\"" . url('/admin/districts') . "/" . $dd->id . "/edit\"><b>" . $dd->district . "</b></a>";
        }
        $cir=Circuit::whereIn('id',$user->circuits)->orderBy('circuit')->get();
        foreach ($cir as $cc){
            $circuits[]="<a href=\"" . url('/admin/circuits') . "/" . $cc->id . "/edit\"><b>" . $cc->circuit . "</b></a>";
        }
        $soc=Society::whereIn('id',$user->societies)->orderBy('society')->get();
        foreach ($soc as $ss){
            $societies[]="<a href=\"" . url('/admin/societies') . "/" . $ss->id . "/edit\"><b>" . $ss->society . "</b></a>";
        }
        return [
            'user_name' => $user->name,
            'is_super_admin' => false,
            'districts' => $districts ?? [],
            'circuits' => $circuits ?? [],
            'societies' => $societies ?? []
        ];
    }

}