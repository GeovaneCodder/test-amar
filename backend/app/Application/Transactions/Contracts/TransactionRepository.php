<?php

namespace App\Application\Transactions\Contracts;

use App\Application\Dashboard\DTOs\DashboardSummaryData;
use App\Application\Transactions\DTOs\TransactionData;
use App\Application\Transactions\DTOs\TransactionFilterData;
use App\Domain\Transactions\Entities\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepository
{
    public function create(Transaction $transaction): TransactionData;

    public function paginateForUser(int $userId, TransactionFilterData $filters): LengthAwarePaginator;

    public function dashboardTotals(int $userId): DashboardSummaryData;
}
