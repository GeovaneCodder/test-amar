<?php

namespace App\Infrastructure\Cache;

use App\Application\Dashboard\DTOs\DashboardSummaryData;
use App\Domain\Transactions\ValueObjects\Money;
use Illuminate\Support\Facades\Cache;

final class RedisDashboardCache
{
    public function remember(int $userId, callable $callback): DashboardSummaryData
    {
        $key = "dashboard:summary:v3:{$userId}";
        $cached = Cache::remember(
            $key,
            now()->addMinutes(5),
            fn (): array => $this->toCacheValue($callback()),
        );

        if (! $this->isValidCacheValue($cached)) {
            Cache::forget($key);
            $summary = $callback();
            Cache::put($key, $this->toCacheValue($summary), now()->addMinutes(5));

            return $summary;
        }

        return new DashboardSummaryData(
            Money::fromMinor($cached['income_minor']),
            Money::fromMinor($cached['expenses_minor']),
            Money::fromMinor($cached['balance_minor']),
        );
    }

    private function toCacheValue(DashboardSummaryData $summary): array
    {
        return [
            'income_minor' => $summary->income->minorUnits,
            'expenses_minor' => $summary->expenses->minorUnits,
            'balance_minor' => $summary->balance->minorUnits,
        ];
    }

    private function isValidCacheValue(mixed $value): bool
    {
        return is_array($value)
            && isset($value['income_minor'], $value['expenses_minor'], $value['balance_minor'])
            && is_int($value['income_minor'])
            && is_int($value['expenses_minor'])
            && is_int($value['balance_minor']);
    }
}
