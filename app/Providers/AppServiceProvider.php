<?php

namespace App\Providers;

use App\Livewire\PreachingPlan;
use App\Models\Circuit;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $circuits = cache()->remember('all_circuits', now()->addHours(12), function () {
                return Circuit::orderBy('circuit')->get();
            });
            $view->with('circuits', $circuits);
        });
        Livewire::component('preaching-plan', PreachingPlan::class);
    }
}
