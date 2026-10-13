<?php

namespace App\Application\Imports\Actions;

use App\Application\Imports\Contracts\FileFingerprint;
use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\ImportData;
use App\Application\Imports\DTOs\StartImportData;
use App\Domain\Imports\Enums\ImportStatus;
use App\Jobs\ProcessImportChunkJob;

final class StartImport
{
    public function __construct(
        private readonly ImportRepository $imports,
        private readonly FileFingerprint $fingerprint,
    ) {}

    public function execute(StartImportData $data): ImportData
    {
        $import = $this->imports->create(
            $data->userId,
            $data->filePath,
            $this->fingerprint->forFile($data->filePath),
        );

        if ($import->status === ImportStatus::Pending) {
            ProcessImportChunkJob::dispatch($import->id);
        }

        return $import;
    }
}
