<?php

namespace App\Domain\Transactions\Entities;

use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Transaction
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public DateTimeImmutable $date,
        public string $description,
        public Money $amount,
        public TransactionType $type,
        public ?int $importId = null,
    ) {
        if ($userId < 1 || ($importId !== null && $importId < 1)) {
            throw new InvalidArgumentException('The transaction must belong to a valid user and import.');
        }

        if ($description === '' || mb_strlen($description) > 255) {
            throw new InvalidArgumentException('The description is required and must not exceed 255 characters.');
        }

        if ($amount->minorUnits <= 0) {
            throw new InvalidArgumentException('The transaction amount must be greater than zero.');
        }
    }
}
