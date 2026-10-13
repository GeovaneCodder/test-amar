<?php

namespace App\Application\Imports\DTOs;

use App\Domain\Imports\Entities\Import;
use App\Domain\Imports\Enums\ImportStatus;
use DateTimeImmutable;

final readonly class ImportData
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
        public array $errors,
        public ?DateTimeImmutable $createdAt,
    ) {}

    public static function fromEntity(Import $import): self
    {
        return new self(
            $import->id,
            $import->userId,
            $import->filePath,
            $import->status,
            $import->totalRows,
            $import->processedRows,
            $import->importedRows,
            $import->failedRows,
            $import->errors,
            $import->createdAt,
        );
    }
}
