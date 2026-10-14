<?php

namespace App\Listeners;

use App\Events\TransactionsImported;
use Illuminate\Support\Facades\Cache;

final class InvalidateDashboardCache
{
    public function handle(TransactionsImported $event): void
    {
        Cache::forget("dashboard:summary:v3:{$event->userId}");
    }
}
