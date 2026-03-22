<?php

use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('preferences')->group(function () {
    Route::get('/',[UserPreferenceController::class, 'show']);
    Route::post('/',[UserPreferenceController::class, 'update']);
    Route::post('/send-pin',[UserPreferenceController::class, 'sendPin']);
    Route::post('/verify-pin',[UserPreferenceController::class, 'verifyPin']);
    Route::post('/push-subscribe',[UserPreferenceController::class, 'pushSubscribe']);
});