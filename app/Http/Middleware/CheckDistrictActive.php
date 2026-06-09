<?php

namespace App\Http\Middleware;

use App\Models\District;
use Closure;
use Illuminate\Http\Request;

class CheckDistrictActive
{
    public function handle(Request $request, Closure $next)
    {
        $districtSlug = $request->route('district');

        if ($districtSlug) {
            $district = District::whereSlug($districtSlug)->first();

            if (!$district || !$district->active) {
                abort(404);
            }
        }

        return $next($request);
    }
}