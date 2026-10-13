<?php

namespace App\Application\Transactions\DTOs;

use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\TransactionPeriod;

final readonly class TransactionFilterData
{
    public function __construct(
        public ?TransactionType $type = null,
        public ?TransactionPeriod $period = null,
        public ?string $search = null,
        public int $perPage = 20,
    ) {}
}
