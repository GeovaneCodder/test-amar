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
            throw new InvalidArgumentException('A transação deve pertencer a um usuário e importação válidos.');
        }

        if ($description === '' || mb_strlen($description) > 255) {
            throw new InvalidArgumentException('A descrição é obrigatória e deve ter até 255 caracteres.');
        }

        if ($amount->minorUnits <= 0) {
            throw new InvalidArgumentException('O valor da transação deve ser maior que zero.');
        }
    }
}
