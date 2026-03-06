<?php

namespace App\Services;

use App\Models\SchoolLocation;

class LocationService
{
    public function validate($lat, $lng)
    {
        $locations = SchoolLocation::all();

        foreach ($locations as $loc) {
            $distance = $this->distance($lat, $lng, $loc->latitude, $loc->longitude);

            if ($distance <= $loc->radius_maks) {
                return [
                    'valid' => true,
                    'location' => $loc,
                    'distance' => $distance
                ];
            }
        }

        return ['valid' => false];
    }

    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
