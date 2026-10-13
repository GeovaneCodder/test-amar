<?php

namespace App\Jobs;

use App\Application\Imports\Actions\ProcessImportChunk;
use App\Application\Imports\Contracts\ImportRepository;
use App\Domain\Imports\Enums\ImportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ProcessImportChunkJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public readonly int $importId)
    {
        $this->afterCommit = true;
    }

    public function handle(ProcessImportChunk $process, ImportRepository $imports): void
    {
        $import = $imports->find($this->importId);

        if ($import === null) {
            return;
        }

        $process->execute($this->importId);
        $import = $imports->find($this->importId);

        if ($import !== null && in_array($import->status, [
            ImportStatus::Completed,
            ImportStatus::CompletedWithErrors,
            ImportStatus::Failed,
        ], true)) {
            Storage::disk('local')->delete($import->filePath);
        }
    }

    public function failed(Throwable $exception, ProcessImportChunk $process): void
    {
        $process->fail($this->importId, $exception);
        $import = app(ImportRepository::class)->find($this->importId);

        if ($import !== null) {
            Storage::disk('local')->delete($import->filePath);
        }
    }
}
