<?php

declare(strict_types=1);

namespace App;

/**
 * Shipping cost in pence: a flat fee per started kilogram, free over a basket threshold, double for express.
 */
final class ShippingCalculator
{
    private const PENCE_PER_KILOGRAM = 150;
    private const FREE_FROM_PENCE = 5000;

    public function cost(int $basketPence, int $weightGrams, bool $express = false): int
    {
        if ($basketPence >= self::FREE_FROM_PENCE && !$express) {
            return 0;
        }

        // Deliberate bug: a started kilogram should round up, but intdiv() rounds down.
        $kilograms = max(1, intdiv($weightGrams, 1000));
        $cost = $kilograms * self::PENCE_PER_KILOGRAM;

        return $express ? $cost * 2 : $cost;
    }
}
