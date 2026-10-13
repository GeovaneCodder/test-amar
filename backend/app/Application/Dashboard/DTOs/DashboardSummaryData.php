<?php

namespace App\Application\Dashboard\DTOs;

use App\Domain\Transactions\ValueObjects\Money;

final readonly class DashboardSummaryData
{
    public function __construct(
        public Money $income,
        public Money $expenses,
        public Money $balance,
    ) {}

    public function toArray(): array
    {
        return [
            'income' => Money::formatMinor($this->income->minorUnits),
            'expenses' => Money::formatMinor($this->expenses->minorUnits),
            'balance' => Money::formatMinor($this->balance->minorUnits),
        ];
    }
}
