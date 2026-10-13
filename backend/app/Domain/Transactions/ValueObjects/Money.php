<?php

namespace App\Domain\Transactions\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(public int $minorUnits) {}

    public static function fromMinor(int $minorUnits): self
    {
        return new self($minorUnits);
    }

    public static function fromDecimal(string|int|float $amount): self
    {
        $amount = str_replace(',', '.', (string) $amount);

        if (! preg_match('/^\d{1,15}(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('O valor monetário é inválido.');
        }

        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return new self(((int) $units * 100) + (int) str_pad($fraction, 2, '0'));
    }

    public static function formatMinor(int|string $minorUnits): string
    {
        $minorUnits = (int) $minorUnits;
        $negative = $minorUnits < 0;
        $minorUnits = abs($minorUnits);

        return ($negative ? '-' : '').intdiv($minorUnits, 100).'.'.str_pad((string) ($minorUnits % 100), 2, '0', STR_PAD_LEFT);
    }
}
