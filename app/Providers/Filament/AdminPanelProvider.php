<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                ->navigationGroup('')
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                'settings' => Action::make('settings')
                    ->label('Settings')
                    ->url('/admin/settings/settings')
                    ->icon('heroicon-o-cog-8-tooth'),
                'my_circuits' => Action::make('my_circuits')
                    ->label('My circuits')
                    ->url(function (){
                        $user=auth()->user();
                        if ($user->circuits){
                            $this->circuit=$user->circuits[0];
                        } else if ($user->societies){
                            $this->circuit=Society::find($user->societies[0])->circuit_id;
                        } else {
                            $this->circuit="";
                        }
                        return '/admin/circuits/' . $this->circuit;
                    })
                    ->icon('heroicon-o-user-group'),
                'back_to_site' => Action::make('back_to_site')
                    ->label('Back to app')
                    ->url('/')
                    ->icon('heroicon-o-arrow-left'),
            ]);
    }
}