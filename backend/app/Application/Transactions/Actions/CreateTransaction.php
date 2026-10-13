<?php

namespace App\Application\Transactions\Actions;

use App\Application\Transactions\Contracts\TransactionRepository;
use App\Application\Transactions\DTOs\CreateTransactionData;
use App\Application\Transactions\DTOs\TransactionData;
use App\Domain\Transactions\Entities\Transaction;

final class CreateTransaction
{
    public function __construct(private readonly TransactionRepository $transactions) {}

    public function execute(CreateTransactionData $data): TransactionData
    {
        $transaction = new Transaction(
            null,
            $data->userId,
            $data->date,
            $data->description,
            $data->amount,
            $data->type,
            $data->importId,
        );

        return $this->transactions->create($transaction);
    }
}
