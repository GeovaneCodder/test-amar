<?php

namespace App\Domain\Transactions\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class TransactionPeriod
{
    public function __construct(
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
    ) {
        if ($from !== null && $to !== null && $from > $to) {
            throw new InvalidArgumentException('The start date must be before or equal to the end date.');
        }
    }

    public static function fromStrings(?string $from, ?string $to): self
    {
        return new self(
            $from === null ? null : new DateTimeImmutable($from),
            $to === null ? null : new DateTimeImmutable($to),
        );
    }
}
