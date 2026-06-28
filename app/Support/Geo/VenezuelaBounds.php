<?php

namespace App\Support\Geo;

final class VenezuelaBounds
{
    // Bounding box amplio de Venezuela continental e islas principales.
    // Se eligió por bajo costo computacional y para rechazar coordenadas claramente fuera del país.
    public const MIN_LAT = 0.5;
    public const MAX_LAT = 12.8;
    public const MIN_LNG = -73.5;
    public const MAX_LNG = -59.5;

    public static function latitudeRule(bool $required = true): string
    {
        $prefix = $required ? 'required' : 'nullable';
        return $prefix . '|numeric|between:' . self::MIN_LAT . ',' . self::MAX_LAT;
    }

    public static function longitudeRule(bool $required = true): string
    {
        $prefix = $required ? 'required' : 'nullable';
        return $prefix . '|numeric|between:' . self::MIN_LNG . ',' . self::MAX_LNG;
    }

    public static function contains(mixed $latitude, mixed $longitude): bool
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        return $lat >= self::MIN_LAT
            && $lat <= self::MAX_LAT
            && $lng >= self::MIN_LNG
            && $lng <= self::MAX_LNG;
    }

    public static function message(): string
    {
        return 'El punto debe estar dentro del territorio de Venezuela.';
    }
}
