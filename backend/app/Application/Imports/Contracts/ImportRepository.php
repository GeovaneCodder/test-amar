<?php

namespace App\Application\Imports\Contracts;

use App\Application\Imports\DTOs\ImportData;
use App\Application\Imports\DTOs\ImportProgressData;

interface ImportRepository
{
    public function create(int $userId, string $filePath, string $fingerprint): ImportData;

    public function find(int $importId): ?ImportData;

    public function updateProgress(int $importId, ImportProgressData $progress): void;

    public function saveTransactions(array $transactions): void;

    public function deleteTransactions(int $importId): int;
}
