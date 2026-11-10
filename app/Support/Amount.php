<?php

namespace App\Support;

use InvalidArgumentException;

class Amount
{
    public static function normalize(string|int|float $amount): string
    {
        $amount = trim((string) $amount);

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        $whole = ltrim($whole, '0') ?: '0';
        $fraction = str_pad($fraction, 2, '0');

        return $whole . '.' . $fraction;
    }

    public static function equals(
        string|int|float $first,
        string|int|float $second
    ): bool {
        return self::normalize($first) === self::normalize($second);
    }
}
