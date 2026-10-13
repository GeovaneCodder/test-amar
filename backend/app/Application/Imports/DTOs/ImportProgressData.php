<?php

namespace App\Application\Imports\DTOs;

use App\Domain\Imports\Enums\ImportStatus;

final readonly class ImportProgressData
{
    public function __construct(
        public ?ImportStatus $status = null,
        public ?int $totalRows = null,
        public ?int $processedRows = null,
        public ?int $importedRows = null,
        public ?int $failedRows = null,
        public ?array $errors = null,
    ) {}

    public function toPersistenceArray(): array
    {
        return array_filter([
            'status' => $this->status?->value,
            'total_rows' => $this->totalRows,
            'processed_rows' => $this->processedRows,
            'imported_rows' => $this->importedRows,
            'failed_rows' => $this->failedRows,
            'errors' => $this->errors,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
