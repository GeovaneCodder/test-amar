<?php

namespace App\Application\Transactions\DTOs;

use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use DateTimeImmutable;

final readonly class CreateTransactionData
{
    public function __construct(
        public int $userId,
        public DateTimeImmutable $date,
        public string $description,
        public Money $amount,
        public TransactionType $type,
        public ?int $importId = null,
    ) {}
}
