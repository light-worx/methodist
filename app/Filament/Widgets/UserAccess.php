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

    protected int | string | array $columnSpan = 'full';
    
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
        if ($user->districts){
            $dis=District::whereIn('id',$user->districts)->orderBy('district')->get();
            foreach ($dis as $dd){
                $districts[]="<a class=\"fi-color fi-color-primary fi-bg-color-600 hover:fi-bg-color-500 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-0 hover:fi-text-color-0 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-md  fi-ac-btn-action\" href=\"" . url('/admin/districts') . "/" . $dd->id . "/edit\"><b>" . $dd->district . "</b></a>";
            }
        }
        if ($user->circuits){
            $cir=Circuit::whereIn('id',$user->circuits)->orderBy('circuit')->get();
            foreach ($cir as $cc){
                $circuits[]="<a class=\"fi-color fi-color-primary fi-bg-color-600 hover:fi-bg-color-500 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-0 hover:fi-text-color-0 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-md  fi-ac-btn-action\" href=\"" . url('/admin/circuits') . "/" . $cc->id . "/edit\"><b>" . $cc->circuit . "</b></a>";
            }
        }
        if ($user->societies){
            $soc=Society::whereIn('id',$user->societies)->orderBy('society')->get();
            foreach ($soc as $ss){
                $societies[]="<a class=\"fi-color fi-color-primary fi-bg-color-600 hover:fi-bg-color-500 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-0 hover:fi-text-color-0 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-md  fi-ac-btn-action\" href=\"" . url('/admin/societies') . "/" . $ss->id . "/edit\"><b>" . $ss->society . "</b></a>";
            }
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