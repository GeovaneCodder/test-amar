<?php

namespace App\Application\Imports\DTOs;

use App\Application\Transactions\DTOs\CreateTransactionData;
use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CsvRow
{
    public function __construct(
        public int $line,
        public string $date,
        public string $description,
        public string $amount,
        public string $type,
        public bool $wellFormed = true,
    ) {}

    public function toTransactionData(int $userId, int $importId): CreateTransactionData
    {
        if (! $this->wellFormed) {
            throw new InvalidArgumentException('The row has an invalid number of columns.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $this->date);

        if ($date === false || $date->format('Y-m-d') !== $this->date) {
            throw new InvalidArgumentException('The date must use the YYYY-MM-DD format.');
        }

        $description = trim($this->description);

        if ($description === '' || mb_strlen($description) > 255) {
            throw new InvalidArgumentException('The description is required and must not exceed 255 characters.');
        }

        $amount = Money::fromDecimal($this->amount);

        if ($amount->minorUnits <= 0) {
            throw new InvalidArgumentException('The amount must be greater than zero.');
        }

        return new CreateTransactionData(
            $userId,
            $date,
            $description,
            $amount,
            TransactionType::fromInput($this->type),
            $importId,
        );
    }
}
