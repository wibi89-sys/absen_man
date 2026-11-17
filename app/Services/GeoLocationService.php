<?php

namespace App\Services;

class GeoLocationService
{
    /**
     * Radius rata-rata bumi dalam meter.
     */
    protected const EARTH_RADIUS_METERS = 6371000;

    /**
     * Hitung jarak lurus (Great-circle distance) menggunakan rumus Haversine (dalam meter).
     */
    public static function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * self::EARTH_RADIUS_METERS, 1);
    }

    /**
     * Periksa apakah koordinat siswa berada dalam batas radius toleransi lokasi madrasah.
     */
    public static function isWithinRadius(
        float $studentLat,
        float $studentLon,
        float $schoolLat,
        float $schoolLon,
        int $radiusMeters
    ): bool {
        $distance = self::calculateDistanceMeters($studentLat, $studentLon, $schoolLat, $schoolLon);

        return $distance <= $radiusMeters;
    }
}
