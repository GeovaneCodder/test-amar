<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\ImportData;
use App\Application\Imports\DTOs\ImportProgressData;
use App\Application\Transactions\DTOs\CreateTransactionData;
use App\Domain\Imports\Entities\Import as ImportEntity;
use App\Domain\Imports\Enums\ImportStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Import;
use Illuminate\Support\Facades\DB;

final class EloquentImportRepository implements ImportRepository
{
    public function create(int $userId, string $filePath, string $fingerprint): ImportData
    {
        $record = Import::query()->firstOrCreate(
            ['user_id' => $userId, 'fingerprint' => $fingerprint],
            ['file_path' => $filePath, 'status' => ImportStatus::Pending],
        );

        if (in_array($record->status, [ImportStatus::Failed->value, ImportStatus::Cancelled->value], true)) {
            $record->update([
                'file_path' => $filePath,
                'status' => ImportStatus::Pending,
                'total_rows' => 0,
                'processed_rows' => 0,
                'imported_rows' => 0,
                'failed_rows' => 0,
                'errors' => [],
            ]);
        }

        return $this->toData($record->refresh());
    }

    public function find(int $importId): ?ImportData
    {
        $record = Import::find($importId);

        return $record === null ? null : $this->toData($record);
    }

    public function updateProgress(int $importId, ImportProgressData $progress): void
    {
        $record = Import::findOrFail($importId);
        $record->update($progress->toPersistenceArray());
    }

    public function saveTransactions(array $transactions): void
    {
        if ($transactions === []) {
            return;
        }

        $now = now();
        $rows = array_map(static fn (CreateTransactionData $transaction): array => [
            'user_id' => $transaction->userId,
            'import_id' => $transaction->importId,
            'transaction_date' => $transaction->date->format('Y-m-d'),
            'description' => $transaction->description,
            'amount_minor' => $transaction->amount->minorUnits,
            'type' => $transaction->type->value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $transactions);

        DB::transaction(static fn () => DB::table('transactions')->insert($rows));
    }

    public function deleteTransactions(int $importId): int
    {
        return DB::table('transactions')->where('import_id', $importId)->delete();
    }

    private function toData(Import $record): ImportData
    {
        return ImportData::fromEntity(new ImportEntity(
            $record->id,
            $record->user_id,
            $record->file_path,
            ImportStatus::from($record->status),
            $record->total_rows,
            $record->processed_rows,
            $record->imported_rows,
            $record->failed_rows,
            $record->errors ?? [],
            $record->created_at?->toDateTimeImmutable(),
        ));
    }
}
