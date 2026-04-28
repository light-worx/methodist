<?php

use App\Filament\Pages\InvitationRegister;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::get('/register/invite/{token}', InvitationRegister::class)->name('invite.register');

// Website routes
Route::middleware(['web'])->controller('\App\Http\Controllers\HomeController')->group(function () {
    Route::get('/', 'home')->name('app.home');
    Route::get('/ideas', 'ideas')->name('app.ideas');
    Route::post('/ideas/store', 'storeidea')->name('app.ideas.store');
    Route::get('/lectionary/{sunday?}','lectionary')->name('app.lectionary');
    Route::get('/ministers/{id}','minister')->name('app.minister');
    Route::get('/preacher/{society}/{servicetime}/{servicedate}','preacher')->name('app.preacher');
    Route::get('/preacherid/{society}/{servicetime}/{servicedate}','preacherid')->name('app.preacherid');
    Route::get('/offline', 'offline')->name('app.offline');
    Route::get('/admin/reports/plan/edit/{record}/{today?}', ['uses'=>'\App\Http\Controllers\HomeController@editplan','as' => 'admin.plan.edit']);
    Route::get('/plan/{id}/{plandate}', ['uses'=>'\App\Http\Controllers\HomeController@pdf','as' => 'reports.plan']);
    Route::get('/register/{id}', ['uses'=>'\App\Http\Controllers\HomeController@register','as' => 'reports.register']);
    if (!str_contains(url()->current(),"admin")){
        Route::get('/{district}', 'district')->name('district');
        Route::get('/{district}/{circuit}', 'circuit')->name('circuit');
        Route::get('/{district}/{circuit}/{society}', 'society')->name('society');
        Route::post('/{society}/location', 'location')->name('society-location');
    }
});