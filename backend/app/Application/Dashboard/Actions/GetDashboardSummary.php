<?php

namespace App\Application\Dashboard\Actions;

use App\Application\Dashboard\DTOs\DashboardSummaryData;
use App\Application\Transactions\Contracts\TransactionRepository;
use App\Infrastructure\Cache\RedisDashboardCache;

final class GetDashboardSummary
{
    public function __construct(
        private readonly TransactionRepository $transactions,
        private readonly RedisDashboardCache $cache,
    ) {}

    public function execute(int $userId): DashboardSummaryData
    {
        return $this->cache->remember(
            $userId,
            fn (): DashboardSummaryData => $this->transactions->dashboardTotals($userId),
        );
    }
}
