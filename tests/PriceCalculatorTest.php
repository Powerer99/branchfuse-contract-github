<?php

declare(strict_types=1);

namespace App\Tests;

use App\PriceCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    public function testSubtotalAddsEveryLine(): void
    {
        $items = [
            ['price' => 250, 'quantity' => 2],
            ['price' => 199, 'quantity' => 1],
        ];

        self::assertSame(699, $this->calculator->subtotal($items));
    }

    public function testSubtotalOfAnEmptyBasketIsZero(): void
    {
        self::assertSame(0, $this->calculator->subtotal([]));
    }

    public function testAverageItemPriceCountsQuantities(): void
    {
        $items = [
            ['price' => 300, 'quantity' => 2],
            ['price' => 600, 'quantity' => 1],
        ];

        self::assertSame(400, $this->calculator->averageItemPrice($items));
    }

    #[Group('known-bugs')]
    public function testAverageItemPriceOfAnEmptyBasketIsZero(): void
    {
        // Errors: averageItemPrice() throws DivisionByZeroError for an empty basket.
        self::assertSame(0, $this->calculator->averageItemPrice([]));
    }

    public function testTaxIsRoundedToTheNearestPenny(): void
    {
        self::assertSame(1200, $this->calculator->withTax(1000, 20));
        self::assertSame(120, $this->calculator->withTax(99, 21));
    }

    public function testPricesAreFormattedInTheChosenCurrency(): void
    {
        // Set by the phpunit-with-variables pipeline; passes for GBP and USD, fails for EUR.
        $currency = getenv('CURRENCY') ?: 'GBP';
        $symbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];

        self::assertSame($symbols[$currency] . '1,234.50', $this->calculator->format(123450, $currency));
    }

    public function testCouponTakesAPercentageOff(): void
    {
        self::assertSame(750, $this->calculator->withCoupon(1000, 25));
    }

    #[Group('known-bugs')]
    public function testCouponNeverMakesThePriceNegative(): void
    {
        // Fails: withCoupon() doesn't cap the discount at 100%.
        self::assertSame(0, $this->calculator->withCoupon(1000, 150));
    }

    public function testSplitBillSharesTheLeftoverPence(): void
    {
        self::assertSame([334, 333, 333], $this->calculator->splitBill(1000, 3));
    }

    #[Group('known-bugs')]
    public function testSplitBillRejectsZeroPeople(): void
    {
        // Fails: splitBill() divides by zero instead of throwing InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->splitBill(1000, 0);
    }
}
