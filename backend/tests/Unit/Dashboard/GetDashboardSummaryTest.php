<?php

namespace Tests\Unit\Dashboard;

use App\Application\Dashboard\Actions\GetDashboardSummary;
use App\Application\Dashboard\DTOs\DashboardSummaryData;
use App\Application\Transactions\Contracts\TransactionRepository;
use App\Domain\Transactions\ValueObjects\Money;
use App\Infrastructure\Cache\RedisDashboardCache;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetDashboardSummaryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_dashboard_summary_is_cached_and_returned_as_domain_money(): void
    {
        $repository = Mockery::mock(TransactionRepository::class);
        $repository->shouldReceive('dashboardTotals')
            ->once()
            ->with(12)
            ->andReturn(new DashboardSummaryData(
                Money::fromMinor(25000),
                Money::fromMinor(7500),
                Money::fromMinor(17500),
            ));

        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(fn (string $key, $ttl, callable $callback): bool => $key === 'dashboard:summary:12'
                && $callback() instanceof DashboardSummaryData)
            ->andReturn(new DashboardSummaryData(
                Money::fromMinor(25000),
                Money::fromMinor(7500),
                Money::fromMinor(17500),
            ));

        $summary = (new GetDashboardSummary($repository, new RedisDashboardCache))->execute(12);

        self::assertSame([
            'income' => '250.00',
            'expenses' => '75.00',
            'balance' => '175.00',
        ], $summary->toArray());
    }
}
