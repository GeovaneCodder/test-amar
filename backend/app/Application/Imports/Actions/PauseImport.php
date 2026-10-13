<?php

namespace App\Application\Imports\Actions;

use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\ImportProgressData;
use App\Domain\Imports\Enums\ImportStatus;
use App\Domain\Imports\Exceptions\ImportNotProcessableException;

final class PauseImport
{
    public function __construct(private readonly ImportRepository $imports) {}

    public function execute(int $importId, int $userId): void
    {
        $import = $this->imports->find($importId);

        if ($import === null || $import->userId !== $userId || $import->status !== ImportStatus::Processing) {
            throw new ImportNotProcessableException('Esta importação não pode ser pausada.');
        }

        $this->imports->updateProgress($importId, new ImportProgressData(status: ImportStatus::Paused));
    }
}
