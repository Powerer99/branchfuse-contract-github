<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Works out what a shopping basket costs. Prices are in pence, so there's no floating-point rounding.
 */
final class PriceCalculator
{
    /**
     * @param list<array{price: int, quantity: int}> $items
     */
    public function subtotal(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    /**
     * @param list<array{price: int, quantity: int}> $items
     */
    public function averageItemPrice(array $items): int
    {
        $count = array_sum(array_column($items, 'quantity'));

        // Deliberate bug: an empty basket divides by zero instead of returning 0.
        return intdiv($this->subtotal($items), $count);
    }

    public function withTax(int $amount, int $ratePercent): int
    {
        return intdiv($amount * (100 + $ratePercent) + 50, 100);
    }

    /**
     * Formats pence as a price, e.g. "£12.50". The currency comes from the CURRENCY pipeline variable in tests.
     */
    public function format(int $pence, string $currency): string
    {
        $amount = number_format($pence / 100, 2);

        return match ($currency) {
            'GBP' => '£' . $amount,
            'USD' => '$' . $amount,
            // Deliberate bug: euros should be "€12.50" like the others, so only a CURRENCY=EUR run fails.
            'EUR' => $amount . ' €',
            default => throw new \InvalidArgumentException("Unknown currency $currency"),
        };
    }

    public function withCoupon(int $amount, int $percentOff): int
    {
        // Deliberate bug: a coupon over 100% should be capped, so the price never goes below zero.
        return $amount - intdiv($amount * $percentOff, 100);
    }

    /**
     * Splits a bill between people; the first people pay the leftover pence.
     *
     * @return list<int>
     */
    public function splitBill(int $amount, int $people): array
    {
        if ($people < 0) {
            throw new InvalidArgumentException('A bill needs at least one person to pay it.');
        }

        // Deliberate bug: zero people isn't rejected above, so this divides by zero.
        $share = intdiv($amount, $people);
        $leftover = $amount % $people;

        $shares = [];
        for ($person = 0; $person < $people; $person++) {
            $shares[] = $share + ($person < $leftover ? 1 : 0);
        }

        return $shares;
    }
}
