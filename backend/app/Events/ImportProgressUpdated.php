<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ImportProgressUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly int $importId,
        public readonly int $processedRows,
        public readonly int $importedRows,
        public readonly int $failedRows,
    ) {}
}
