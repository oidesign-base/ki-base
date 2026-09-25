<?php

declare(strict_types=1);

namespace App\Core;

/** Small validation helpers used by controllers. */
final class Validate
{
    /**
     * Normalise a Polish NIP: removes spaces, dashes and a "PL" prefix.
     * Returns 10 digits, or null when the value is not a valid NIP
     * (wrong length or check digit).
     */
    public static function nip(string $value): ?string
    {
        $digits = preg_replace('/[\s-]/', '', $value) ?? '';
        if (stripos($digits, 'PL') === 0) {
            $digits = substr($digits, 2);
        }
        if (!preg_match('/^\d{10}$/', $digits)) {
            return null;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;
        foreach ($weights as $i => $w) {
            $sum += $w * (int) $digits[$i];
        }
        $check = $sum % 11;

        return $check !== 10 && $check === (int) $digits[9] ? $digits : null;
    }

    /** Format a normalised NIP for display: 123-456-78-90. */
    public static function formatNip(?string $nip): string
    {
        if ($nip === null || !preg_match('/^\d{10}$/', $nip)) {
            return (string) $nip;
        }
        return substr($nip, 0, 3) . '-' . substr($nip, 3, 3) . '-' . substr($nip, 6, 2) . '-' . substr($nip, 8, 2);
    }

    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }
}
