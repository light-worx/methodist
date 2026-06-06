<?php

namespace App\Providers;

use App\Http\Middleware\AdminRoute;
use App\Http\Middleware\CheckPerms;
use App\Livewire\MinistryIdeaForm;
use App\Livewire\PreachingPlan;
use App\Livewire\ServiceDetails;
use App\Models\Circuit;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Lightworx\FilamentPwa\Facades\PwaFieldOptions;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
 

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
        $router = $this->app['router'];
        $router->aliasMiddleware('adminonly', AdminRoute::class);
        $router->aliasMiddleware('checkperms', CheckPerms::class);
        Config::set('livewire.render_on_redirect',false);
        if (Schema::hasTable('filament_settings')){
            Config::set('app.name', setting('site_name'));
            putenv ("DEEPSEEK_API_KEY=" . setting('deepseek_api'));
            Config::set('mail.default',setting('mailer', ['value' => 'smtp','label' => 'Mailer type','category' => 'Email','setting_type' => 'list','options' => ['smtp','mailgun','ses','postmark']]));
            Config::set('mail.mailers.' . setting('mailer') . '.host',setting('mail_host', ['label' => 'Host','category' => 'Email']));
            Config::set('mail.mailers.' . setting('mailer') . '.port',setting('mail_port', ['label' => 'Port','category' => 'Email']));
            Config::set('mail.mailers.' . setting('mailer') . '.username',setting('mail_username', ['label' => 'Username','category' => 'Email']));
            Config::set('mail.mailers.' . setting('mailer') . '.password',setting('mail_password', ['label' => 'Password','setting_type' => 'password','category' => 'Email']));
            Config::set('mail.mailers.' . setting('mailer') . '.encryption',setting('mail_encryption', ['value' => 'ssl','label' => 'Encryption','category' => 'Email','setting_type' => 'list','options' => ['ssl','tls']]));
            Config::set('mail.from.address',setting('mail_from_address', ['label' => 'From address','category' => 'Email']));
            Config::set('mail.from.name',setting('mail_from_name', ['label' => 'From name','category' => 'Email']));    
            Config::set('mail.reply_to.address',setting('mail_from_address', ['label' => 'Reply-to address','category' => 'Email']));
            Config::set('mail.reply_to.name',setting('mail_from_name', ['label' => 'Reply-to name','category' => 'Email']));
        }
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
        Livewire::component('preaching-plan', PreachingPlan::class); 
        Livewire::component('service-details', ServiceDetails::class);
        Livewire::component('ministry-idea-form', MinistryIdeaForm::class);
        PwaFieldOptions::register('circuit_id', fn(?string $search) =>
            Circuit::when(
                is_numeric($search),
                fn($q) => $q->whereKey($search),        // restore: fetch circuit 155 directly
                fn($q) => $q->when($search,
                    fn($q2) => $q2->where('circuit', 'like', "%{$search}%")
                )
            )->limit(50)->pluck('circuit', 'id')->toArray()
        );
    }
    
}
