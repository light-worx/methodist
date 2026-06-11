<?php

/*
|--------------------------------------------------------------------------
| Public Society API Routes
|--------------------------------------------------------------------------
|
| These routes expose read-only, unauthenticated endpoints for external
| sites to query society schedule information.
|
| Add this to routes/api.php (or a dedicated routes/api_public.php file
| that you include from RouteServiceProvider).
|
*/

use App\Http\Controllers\Api\Public\SocietyPublicController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')
    ->middleware(['throttle:public-api'])   // see AppServiceProvider / RouteServiceProvider
    ->group(function () {

        /*
         * Midweek services for a society in a given year.
         *
         * GET /api/public/societies/{societySlug}/midweeks/{year}
         *
         * Example:
         *   GET /api/public/societies/durban-central/midweeks/2025
         *
         * Response:
         *   {
         *     "society": "Durban Central",
         *     "circuit": "Durban Circuit",
         *     "year": 2025,
         *     "midweeks": [
         *       { "date": "2025-01-01", "day": "Wednesday", "time": "19:00", "service_type": "Midweek Service" },
         *       ...
         *     ]
         *   }
         */
        Route::get(
            'societies/{societyId}/midweeks/{year}',
            [SocietyPublicController::class, 'midweeks']
        )->where(['societyId' => '[0-9\-]+', 'year' => '[0-9]{4}']);


        /*
         * All preaching appointments for a society in a given year.
         *
         * GET /api/public/societies/{societySlug}/preachers/{year}
         *
         * Example:
         *   GET /api/public/societies/durban-central/preachers/2025
         *
         * Response:
         *   {
         *     "society": "Durban Central",
         *     "year": 2025,
         *     "appointments": [
         *       {
         *         "date": "2025-01-05",
         *         "day": "Sunday",
         *         "time": "09:30",
         *         "service_type": "Morning Worship",
         *         "preacher": "Rev John Smith"
         *       },
         *       ...
         *     ]
         *   }
         */
        Route::get(
            'societies/{societyId}/preachers/{year}',
            [SocietyPublicController::class, 'preachers']
        )->where(['societyId' => '[0-9\-]+', 'year' => '[0-9]{4}']);

        Route::get('preacher/{societyId}/{servicedate}/{servicetime}',[SocietyPublicController::class, 'preacher']);

    });