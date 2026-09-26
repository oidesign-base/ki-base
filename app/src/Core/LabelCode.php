<?php

declare(strict_types=1);

namespace App\Core;

/**
 * QR label codes: 2-letter prefix + 6-digit number + check digit,
 * e.g. "KI9999011", shown on the label as "KI 999 901 1".
 *
 * The check digit uses the Damm algorithm, which detects every single-digit
 * error and every swap of two adjacent digits. It covers the prefix too:
 * before the calculation each letter becomes two digits (A=10 ... Z=35).
 */
final class LabelCode
{
    public const MAX_NUMBER = 999999;

    private const DAMM = [
        [0, 3, 1, 7, 5, 9, 8, 6, 4, 2],
        [7, 0, 9, 2, 1, 5, 4, 8, 6, 3],
        [4, 2, 0, 6, 8, 7, 1, 3, 5, 9],
        [1, 7, 5, 0, 9, 8, 3, 4, 2, 6],
        [6, 1, 2, 3, 0, 4, 5, 9, 7, 8],
        [3, 6, 7, 4, 2, 0, 9, 5, 8, 1],
        [5, 8, 6, 9, 7, 2, 0, 1, 3, 4],
        [8, 9, 4, 5, 3, 6, 2, 0, 1, 7],
        [9, 4, 3, 8, 6, 1, 7, 2, 0, 5],
        [2, 5, 8, 1, 4, 3, 6, 7, 9, 0],
    ];

    /** Full code for a prefix and a sequence number (1 ... 999999). */
    public static function make(string $prefix, int $number): string
    {
        if (!preg_match('/^[A-Z]{2}$/', $prefix) || $number < 0 || $number > self::MAX_NUMBER) {
            throw new \InvalidArgumentException('Invalid label code parts');
        }
        $digits = sprintf('%06d', $number);
        return $prefix . $digits . self::checkDigit($prefix, $digits);
    }

    public static function checkDigit(string $prefix, string $digits): int
    {
        return self::damm(self::toDigits($prefix) . $digits);
    }

    /** Format and check digit are both correct. */
    public static function isValid(string $code): bool
    {
        return preg_match('/^[A-Z]{2}[0-9]{7}$/', $code) === 1
            && self::damm(self::toDigits(substr($code, 0, 2)) . substr($code, 2)) === 0;
    }

    /**
     * Code typed by hand or read from a QR: spaces and dashes removed,
     * letters capitalised. Returns null when it is not a valid code.
     */
    public static function normalize(string $input): ?string
    {
        $code = strtoupper(preg_replace('/[\s-]+/', '', $input) ?? '');
        return self::isValid($code) ? $code : null;
    }

    /** "KI9999011" -> "KI 999 901 1" (as printed on the label). */
    public static function format(string $code): string
    {
        if (strlen($code) !== 9) {
            return $code;
        }
        return substr($code, 0, 2) . ' ' . substr($code, 2, 3) . ' ' . substr($code, 5, 3) . ' ' . $code[8];
    }

    public static function number(string $code): int
    {
        return (int) substr($code, 2, 6);
    }

    private static function toDigits(string $prefix): string
    {
        $out = '';
        foreach (str_split($prefix) as $letter) {
            $out .= (string) (ord($letter) - ord('A') + 10);
        }
        return $out;
    }

    private static function damm(string $digits): int
    {
        $interim = 0;
        foreach (str_split($digits) as $d) {
            $interim = self::DAMM[$interim][(int) $d];
        }
        return $interim;
    }
}
