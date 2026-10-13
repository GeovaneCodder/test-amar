<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Dashboard\DTOs\DashboardSummaryData;
use App\Application\Transactions\Contracts\TransactionRepository;
use App\Application\Transactions\DTOs\TransactionData;
use App\Application\Transactions\DTOs\TransactionFilterData;
use App\Domain\Transactions\Entities\Transaction as TransactionEntity;
use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Models\Transaction;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentTransactionRepository implements TransactionRepository
{
    public function create(TransactionEntity $transaction): TransactionData
    {
        $record = Transaction::query()->create([
            'user_id' => $transaction->userId,
            'import_id' => $transaction->importId,
            'transaction_date' => $transaction->date->format('Y-m-d'),
            'description' => $transaction->description,
            'amount_minor' => $transaction->amount->minorUnits,
            'type' => $transaction->type->value,
        ]);

        return TransactionData::fromEntity($this->toEntity($record), $record->created_at?->toDateTimeImmutable());
    }

    public function paginateForUser(int $userId, TransactionFilterData $filters): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->where('user_id', $userId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($filters->type !== null) {
            $query->where('type', $filters->type->value);
        }

        if ($filters->period?->from !== null) {
            $query->whereDate('transaction_date', '>=', $filters->period->from->format('Y-m-d'));
        }

        if ($filters->period?->to !== null) {
            $query->whereDate('transaction_date', '<=', $filters->period->to->format('Y-m-d'));
        }

        if ($filters->search !== null && $filters->search !== '') {
            $query->where('description', 'like', '%'.$filters->search.'%');
        }

        return $query->paginate($filters->perPage)->through(
            fn (Transaction $record): TransactionData => TransactionData::fromEntity(
                $this->toEntity($record),
                $record->created_at?->toDateTimeImmutable(),
            ),
        );
    }

    public function dashboardTotals(int $userId): DashboardSummaryData
    {
        $totals = Transaction::query()
            ->where('user_id', $userId)
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount_minor ELSE 0 END), 0) AS income_minor', ['income'])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount_minor ELSE 0 END), 0) AS expenses_minor', ['expense'])
            ->firstOrFail();

        $income = (int) $totals->income_minor;
        $expenses = (int) $totals->expenses_minor;

        return new DashboardSummaryData(
            Money::fromMinor($income),
            Money::fromMinor($expenses),
            Money::fromMinor($income - $expenses),
        );
    }

    private function toEntity(Transaction $record): TransactionEntity
    {
        return new TransactionEntity(
            $record->id,
            $record->user_id,
            new DateTimeImmutable($record->transaction_date->format('Y-m-d')),
            $record->description,
            Money::fromMinor($record->amount_minor),
            TransactionType::from($record->type),
            $record->import_id,
        );
    }
}
