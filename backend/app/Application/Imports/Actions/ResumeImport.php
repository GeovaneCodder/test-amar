<?php

namespace App\Application\Imports\Actions;

use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\ImportProgressData;
use App\Domain\Imports\Enums\ImportStatus;
use App\Domain\Imports\Exceptions\ImportNotProcessableException;
use App\Jobs\ProcessImportChunkJob;

final class ResumeImport
{
    public function __construct(private readonly ImportRepository $imports) {}

    public function execute(int $importId, int $userId): void
    {
        $import = $this->imports->find($importId);

        if ($import === null || $import->userId !== $userId || ! $import->canProcess()) {
            throw new ImportNotProcessableException('Esta importação não pode ser retomada.');
        }

        $this->imports->updateProgress($importId, new ImportProgressData(status: ImportStatus::Pending));
        ProcessImportChunkJob::dispatch($importId);
    }
}
