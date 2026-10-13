<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class TransactionsImported
{
    use Dispatchable;

    public function __construct(public readonly int $userId) {}
}
