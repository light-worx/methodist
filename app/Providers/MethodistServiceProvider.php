<?php namespace App\Providers;
/*
use App\Livewire\ServiceDetails;
use App\Http\Middleware\AdminRoute;
use App\Http\Middleware\CheckPerms;
use App\Livewire\MinistryIdeaForm;
use App\Livewire\PreachingPlan;
use Illuminate\Support\ServiceProvider;
use App\Methodist;
use App\Models\Circuit;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\View;

class MethodistServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'methodist');
        Paginator::useBootstrapFive();
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
        if (Schema::hasTable('settings')) {
            Config::set('app.name',setting('general.site_name')); 
        }
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
        Blade::componentNamespace('Bishopm\\Methodist\\Resources\\Views\\Components', 'methodist');
        Config::set('auth.providers.users.model','App\Models\User');
        Config::set('filament-spatie-roles-permissions.scope_to_tenant',false);
        Config::set('filament-spatie-roles-permissions.clusters.permissions',\App\Filament\Clusters\Settings::class);
        Config::set('filament-spatie-roles-permissions.clusters.roles',\App\Filament\Clusters\Settings::class);
        Config::set('filament-spatie-roles-permissions.should_redirect_to_index.roles.after_edit',true);
        Config::set('filament-spatie-roles-permissions.should_redirect_to_index.roles.after_create',true);
        Config::set('filament-spatie-roles-permissions.should_redirect_to_index.permissions.after_edit',true);
        Config::set('filament-spatie-roles-permissions.should_redirect_to_index.permissions.after_create',true);
        Config::set('filament-spatie-roles-permissions.guard_names',['web'=>'web']);
        Config::set('filament-spatie-roles-permissions.default_guard_name','web');
        Config::set('filament-spatie-roles-permissions.generator.guard_names',['web'=>'web']);
        Config::set('filament-spatie-roles-permissions.generator.model_directories',[base_path('vendor/bishopm/methodist/src/Models')]);
        Config::set('filament-spatie-roles-permissions.generator.user_model', \App\Models\User::class);
        Config::set('filament-spatie-roles-permissions.generator.policies_namespace','App\Filament\Policies');
        Config::set('livewire.render_on_redirect',false);
        putenv ("DEEPSEEK_API_KEY=" . setting('general.deepseek_api'));
        Livewire::component('preaching-plan', PreachingPlan::class); 
        Livewire::component('service-details', ServiceDetails::class);
        Livewire::component('ministry-idea-form', MinistryIdeaForm::class);
        Gate::policy(Role::class, \App\Filament\Policies\RolePolicy::class);
        Gate::policy(Permission::class, \App\Filament\Policies\PermissionPolicy::class);
        Gate::policy(\App\Models\Circuit::class, \App\Filament\Policies\CircuitPolicy::class);
        Gate::policy(\App\Models\District::class, \App\Filament\Policies\DistrictPolicy::class);
        Gate::policy(\App\Models\Society::class, \App\Filament\Policies\SocietyPolicy::class);
        Gate::policy(\App\Models\Person::class, \App\Filament\Policies\PersonPolicy::class);
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true: null;     
        });
        View::composer('*', function ($view) {
            $circuits = cache()->remember('all_circuits', now()->addHours(12), function () {
                return Circuit::orderBy('circuit')->get();
            });
            $view->with('circuits', $circuits);
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/methodist.php', 'methodist');
        $this->app->singleton('methodist', function ($app) {
            return new Methodist;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['methodist'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../../config/methodist.php' => config_path('methodist.php'),
        ], 'methodist.config');

        // Publishing the views.
        // $this->publishes([
        //    __DIR__.'/../Resources' => public_path('vendor/bishopm'),
        // ], 'methodist.views');

        // Publishes assets.
        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('methodist'),
          ], 'assets');
        

        // Registering package commands.
        $this->commands([
            'App\Console\Commands\InstallMethodist'
        ]);
    }
}
*/