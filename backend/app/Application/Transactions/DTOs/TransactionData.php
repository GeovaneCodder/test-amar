<?php

namespace App\Application\Transactions\DTOs;

use App\Domain\Transactions\Entities\Transaction;
use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use DateTimeImmutable;

final readonly class TransactionData
{
    public function __construct(
        public int $id,
        public int $userId,
        public DateTimeImmutable $date,
        public string $description,
        public Money $amount,
        public TransactionType $type,
        public ?int $importId,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public static function fromEntity(Transaction $transaction, ?DateTimeImmutable $createdAt = null): self
    {
        return new self(
            $transaction->id ?? 0,
            $transaction->userId,
            $transaction->date,
            $transaction->description,
            $transaction->amount,
            $transaction->type,
            $transaction->importId,
            $createdAt,
        );
    }
}
