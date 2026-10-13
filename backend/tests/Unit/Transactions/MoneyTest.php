<?php

namespace Tests\Unit\Transactions;

use App\Domain\Transactions\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_converts_decimal_money_without_floating_point_rounding(): void
    {
        self::assertSame(12345, Money::fromDecimal('123.45')->minorUnits);
        self::assertSame(120, Money::fromDecimal('1,2')->minorUnits);
        self::assertSame('123.45', Money::formatMinor(12345));
        self::assertSame('-0.25', Money::formatMinor(-25));
    }

    public function test_it_rejects_invalid_money_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimal('-1.00');
    }
}
