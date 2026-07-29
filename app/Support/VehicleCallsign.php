<?php

namespace App\Support;

class VehicleCallsign
{
    public static function normalize($value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $parts = preg_split('/\D+/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) !== 2) {
            return null;
        }

        $prefix = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $number = str_pad($parts[1], 3, '0', STR_PAD_LEFT);
        $callsign = $prefix.'-'.$number;

        return preg_match('/^\d{2}-\d{3}$/', $callsign) ? $callsign : null;
    }
}
