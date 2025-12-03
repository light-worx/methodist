<?php

namespace App\Providers;

use App\Http\Middleware\AdminRoute;
use App\Http\Middleware\CheckPerms;
use App\Livewire\MinistryIdeaForm;
use App\Livewire\PreachingPlan;
use App\Livewire\ServiceDetails;
use App\Models\Circuit;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Config;

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
    public function boot($settings): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('adminonly', AdminRoute::class);
        $router->aliasMiddleware('checkperms', CheckPerms::class);
        View::composer('*', function ($view) {
            $circuits = cache()->remember('all_circuits', now()->addHours(12), function () {
                return Circuit::orderBy('circuit')->get();
            });
            $view->with('circuits', $circuits);
        });
        Config::set('livewire.render_on_redirect',false);
        putenv ("DEEPSEEK_API_KEY=" . $settings->deepseek_api);
        Livewire::component('preaching-plan', PreachingPlan::class); 
        Livewire::component('service-details', ServiceDetails::class);
        Livewire::component('ministry-idea-form', MinistryIdeaForm::class);
    }
}
