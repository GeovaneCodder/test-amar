<?php

namespace App\Domain\Transactions\Enums;

use InvalidArgumentException;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public static function fromInput(string $type): self
    {
        return match (mb_strtolower(trim($type))) {
            'income', 'receita' => self::Income,
            'expense', 'despesa' => self::Expense,
            default => throw new InvalidArgumentException('O tipo deve ser Receita ou Despesa.'),
        };
    }

    public function isIncome(): bool
    {
        return $this === self::Income;
    }
}
