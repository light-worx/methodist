<?php

namespace App\Services;

use App\Models\Circuit;
use App\Models\District;

class MapCoordinateService
{
    public static function resolve(?int $circuitId): array
    {
        $default = [
            'latitude' => setting('default_latitude', -26.180611),
            'longitude' => setting('default_longitude', 28.1046067),
        ];

        if (!$circuitId) {
            return $default;
        }

        $circuit = Circuit::with([
            'societies' => fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude')
        ])->find($circuitId);

        if (!$circuit) {
            return $default;
        }

        if ($circuit->societies->isNotEmpty()) {
            $s = $circuit->societies->last();

            return [
                'latitude' => (float) $s->latitude,
                'longitude' => (float) $s->longitude,
            ];
        }

        $district = District::find($circuit->district_id);

        if ($district && $district->latitude && $district->longitude) {
            return [
                'latitude' => (float) $district->latitude,
                'longitude' => (float) $district->longitude,
            ];
        }

        return $default;
    }
}