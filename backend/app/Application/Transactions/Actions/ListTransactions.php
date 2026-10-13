<?php

namespace App\Application\Transactions\Actions;

use App\Application\Transactions\Contracts\TransactionRepository;
use App\Application\Transactions\DTOs\TransactionFilterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTransactions
{
    public function __construct(private readonly TransactionRepository $transactions) {}

    public function execute(int $userId, TransactionFilterData $filters): LengthAwarePaginator
    {
        return $this->transactions->paginateForUser($userId, $filters);
    }
}
