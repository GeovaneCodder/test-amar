<?php

namespace App\Providers;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Imports\Contracts\CsvReader;
use App\Application\Imports\Contracts\FileFingerprint;
use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Transactions\Contracts\TransactionRepository;
use App\Events\TransactionsImported;
use App\Infrastructure\Csv\NativeCsvReader;
use App\Infrastructure\Csv\Sha256FileFingerprint;
use App\Infrastructure\Persistence\Eloquent\Models\Transaction;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentImportRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTransactionRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Listeners\InvalidateDashboardCache;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionRepository::class, EloquentTransactionRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ImportRepository::class, EloquentImportRepository::class);
        $this->app->bind(CsvReader::class, NativeCsvReader::class);
        $this->app->bind(FileFingerprint::class, Sha256FileFingerprint::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(TransactionsImported::class, InvalidateDashboardCache::class);
        Transaction::observe(TransactionObserver::class);
    }
}
