<?php

namespace App\Domain\Imports\Entities;

use App\Domain\Imports\Enums\ImportStatus;
use DateTimeImmutable;

final readonly class Import
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $filePath,
        public ImportStatus $status,
        public int $totalRows,
        public int $processedRows,
        public int $importedRows,
        public int $failedRows,
        public array $errors = [],
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function canProcess(): bool
    {
        return $this->status->canProcess();
    }
}
