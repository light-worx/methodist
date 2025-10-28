<?php

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

Route::get('/manifest.json', fn() => response()->view('pwa.manifest')->header('Content-Type', 'application/json'));
Route::get('/service-worker.js', fn () => response()->view('pwa.service-worker')->header('Content-Type', 'application/javascript'));

// Website routes
Route::middleware(['web'])->controller('\App\Http\Controllers\HomeController')->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/ideas', 'ideas')->name('ideas');
    Route::post('/ideas/store', 'storeidea')->name('ideas.store');
    Route::get('/lectionary/{sunday?}','lectionary')->name('lectionary');
    Route::get('/ministers/{id}','minister')->name('minister');
    Route::get('/offline', 'offline')->name('offline');
    Route::get('/admin/reports/plan/edit/{id}', ['uses'=>'\App\Http\Controllers\HomeController@editplan','as' => 'admin.plan.edit']);
    Route::get('/plan/{id}/{plandate}', ['uses'=>'\App\Http\Controllers\HomeController@pdf','as' => 'reports.plan']);
    if (!str_contains(url()->current(),"admin")){
        Route::get('/{district}', 'district')->name('district');
        Route::get('/{district}/{circuit}', 'circuit')->name('circuit');
        Route::get('/{district}/{circuit}/{society}', 'society')->name('society');
    }
});