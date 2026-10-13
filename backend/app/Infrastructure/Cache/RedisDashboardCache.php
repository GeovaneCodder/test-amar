<?php

namespace App\Infrastructure\Cache;

use App\Application\Dashboard\DTOs\DashboardSummaryData;
use Illuminate\Support\Facades\Cache;

final class RedisDashboardCache
{
    public function remember(int $userId, callable $callback): DashboardSummaryData
    {
        $summary = Cache::remember(
            "dashboard:summary:{$userId}",
            now()->addMinutes(5),
            $callback,
        );

        if (! $summary instanceof DashboardSummaryData) {
            throw new \UnexpectedValueException('O cache do dashboard contém um formato inválido.');
        }

        return $summary;
    }
}
