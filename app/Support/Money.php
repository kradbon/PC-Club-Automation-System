<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function cents(string|int $amount): int
    {
        $value = (string) $amount;

        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $parts)) {
            throw new InvalidArgumentException('Money amounts must be positive decimal values with at most two decimal places.');
        }

        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '0', 2, '0');
    }

    public static function decimal(int $cents): string
    {
        $whole = intdiv(abs($cents), 100);
        $fraction = str_pad((string) (abs($cents) % 100), 2, '0', STR_PAD_LEFT);

        return ($cents < 0 ? '-' : '').$whole.'.'.$fraction;
    }
}
