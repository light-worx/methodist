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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
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
        if (Schema::hasTable('filament_settings')){
            Config::set('app.name', setting('site_name'));
            putenv ("DEEPSEEK_API_KEY=" . setting('deepseek_api'));
        }
        Livewire::component('preaching-plan', PreachingPlan::class); 
        Livewire::component('service-details', ServiceDetails::class);
        Livewire::component('ministry-idea-form', MinistryIdeaForm::class);
    }
}
