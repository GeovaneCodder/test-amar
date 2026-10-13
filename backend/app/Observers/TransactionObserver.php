<?php

namespace App\Observers;

use App\Events\TransactionsImported;
use App\Infrastructure\Persistence\Eloquent\Models\Transaction;

final class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        TransactionsImported::dispatch($transaction->user_id);
    }

    public function updated(Transaction $transaction): void
    {
        TransactionsImported::dispatch($transaction->user_id);
    }

    public function deleted(Transaction $transaction): void
    {
        TransactionsImported::dispatch($transaction->user_id);
    }
}
