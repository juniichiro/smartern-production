<?php

namespace App\Services;

class GeofenceService
{
    /**
     * Calculate the distance between two points in meters using the Haversine formula.
     *
     * @param  float  $lat1  Latitude of point 1
     * @param  float  $lon1  Longitude of point 1
     * @param  float  $lat2  Latitude of point 2
     * @param  float  $lon2  Longitude of point 2
     * @return float Distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius of the earth in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if a location is within a specific radius of a center point.
     *
     * @param  float  $userLat
     * @param  float  $userLon
     * @param  float  $centerLat
     * @param  float  $centerLon
     * @param  int  $radiusMeters
     * @return array [bool isInside, float distance]
     */
    public function isWithinRange($userLat, $userLon, $centerLat, $centerLon, $radiusMeters)
    {
        $distance = $this->calculateDistance($userLat, $userLon, $centerLat, $centerLon);

        return [
            'is_inside' => $distance <= $radiusMeters,
            'distance' => round($distance, 2),
        ];
    }
}
