<?php

namespace App\Application\Imports\Actions;

use App\Application\Imports\Contracts\CsvReader;
use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\ImportProgressData;
use App\Domain\Imports\Enums\ImportStatus;
use App\Domain\Imports\Exceptions\ImportNotProcessableException;
use App\Events\ImportFinished;
use App\Events\ImportProgressUpdated;
use App\Events\TransactionsImported;
use InvalidArgumentException;
use Throwable;

final class ProcessImportChunk
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly ImportRepository $imports,
        private readonly CsvReader $csvReader,
    ) {}

    public function execute(int $importId): void
    {
        $import = $this->imports->find($importId);

        if ($import === null || $import->status->isFinished() || $import->status === ImportStatus::Processing) {
            return;
        }

        if ($import->status === ImportStatus::Paused || $import->status === ImportStatus::Cancelled) {
            return;
        }

        $this->imports->deleteTransactions($importId);
        $this->imports->updateProgress($importId, new ImportProgressData(
            status: ImportStatus::Processing,
            totalRows: 0,
            processedRows: 0,
            importedRows: 0,
            failedRows: 0,
            errors: [],
        ));

        $processed = 0;
        $imported = 0;
        $failed = 0;
        $errors = [];
        $batch = [];

        try {
            foreach ($this->csvReader->read($import->filePath) as $row) {
                $processed++;

                try {
                    $batch[] = $row->toTransactionData($import->userId, $importId);
                } catch (InvalidArgumentException $exception) {
                    $failed++;

                    if (count($errors) < 100) {
                        $errors[] = ['row' => $row->line, 'message' => $exception->getMessage()];
                    }
                }

                if (count($batch) >= self::BATCH_SIZE) {
                    $this->imports->saveTransactions($batch);
                    $imported += count($batch);
                    $batch = [];
                    $this->updateProgress($importId, $processed, $imported, $failed, $errors);

                    $currentImport = $this->imports->find($importId);

                    if ($currentImport === null || $currentImport->status !== ImportStatus::Processing) {
                        $this->finishInterruptedImport($importId, $currentImport?->status, $processed, $imported, $failed, $errors);

                        return;
                    }
                }
            }

            $currentImport = $this->imports->find($importId);

            if ($currentImport === null || $currentImport->status !== ImportStatus::Processing) {
                $this->finishInterruptedImport($importId, $currentImport?->status, $processed, $imported, $failed, $errors);

                return;
            }

            if ($batch !== []) {
                $this->imports->saveTransactions($batch);
                $imported += count($batch);
            }

            $this->imports->updateProgress($importId, new ImportProgressData(
                status: $failed > 0 ? ImportStatus::CompletedWithErrors : ImportStatus::Completed,
                totalRows: $processed,
                processedRows: $processed,
                importedRows: $imported,
                failedRows: $failed,
                errors: $errors,
            ));

            TransactionsImported::dispatch($import->userId);
            $this->broadcastFinished($importId);
        } catch (ImportNotProcessableException $exception) {
            $this->markFailed($importId, $exception->getMessage());
            $this->broadcastFinished($importId);
        } catch (Throwable $exception) {
            $this->imports->deleteTransactions($importId);
            $this->markFailed($importId, 'Failed to process the CSV: '.$exception->getMessage());
            throw $exception;
        }
    }

    public function fail(int $importId, Throwable $exception): void
    {
        $import = $this->imports->find($importId);

        if ($import === null || $import->status->isFinished()) {
            return;
        }

        $this->imports->deleteTransactions($importId);
        $this->markFailed($importId, 'The job exhausted its retry attempts: '.$exception->getMessage());

        TransactionsImported::dispatch($import->userId);
        $this->broadcastFinished($importId);
    }

    private function updateProgress(int $importId, int $processed, int $imported, int $failed, array $errors): void
    {
        $this->imports->updateProgress($importId, new ImportProgressData(
            totalRows: $processed,
            processedRows: $processed,
            importedRows: $imported,
            failedRows: $failed,
            errors: $errors,
        ));

        ImportProgressUpdated::dispatch($importId, $processed, $imported, $failed);
    }

    private function markFailed(int $importId, string $message): void
    {
        $import = $this->imports->find($importId);

        if ($import === null) {
            return;
        }

        $errors = $import->errors;
        $errors[] = ['row' => null, 'message' => $message];
        $this->imports->updateProgress($importId, new ImportProgressData(
            status: ImportStatus::Failed,
            errors: array_slice($errors, 0, 100),
        ));
    }

    private function broadcastFinished(int $importId): void
    {
        $import = $this->imports->find($importId);

        if ($import !== null) {
            ImportFinished::dispatch($import->userId, $import);
        }
    }

    private function finishInterruptedImport(
        int $importId,
        ?ImportStatus $status,
        int $processed,
        int $imported,
        int $failed,
        array $errors,
    ): void {
        if ($status === ImportStatus::Paused) {
            $this->imports->updateProgress($importId, new ImportProgressData(
                totalRows: $processed,
                processedRows: $processed,
                importedRows: $imported,
                failedRows: $failed,
                errors: $errors,
            ));

            if ($imported > 0) {
                $import = $this->imports->find($importId);

                if ($import !== null) {
                    TransactionsImported::dispatch($import->userId);
                }
            }

            return;
        }

        if ($status === ImportStatus::Cancelled) {
            $deleted = $this->imports->deleteTransactions($importId);

            if ($deleted > 0) {
                $import = $this->imports->find($importId);

                if ($import !== null) {
                    TransactionsImported::dispatch($import->userId);
                }
            }
        }
    }
}
