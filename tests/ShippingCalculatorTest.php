<?php

declare(strict_types=1);

namespace App\Tests;

use App\ShippingCalculator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ShippingCalculatorTest extends TestCase
{
    public function testLightParcelsPayForOneKilogram(): void
    {
        self::assertSame(150, (new ShippingCalculator())->cost(1000, 400));
    }

    public function testShippingIsFreeOverTheThreshold(): void
    {
        self::assertSame(0, (new ShippingCalculator())->cost(5000, 3000));
    }

    public function testExpressIsNeverFreeAndCostsDouble(): void
    {
        self::assertSame(600, (new ShippingCalculator())->cost(6000, 2000, express: true));
    }

    #[Group('known-bugs')]
    public function testAStartedKilogramIsChargedInFull(): void
    {
        // Fails: 2.5 kg should cost 3 kg, but cost() rounds down to 2 kg.
        self::assertSame(450, (new ShippingCalculator())->cost(1000, 2500));
    }
}
