<?php

namespace App\Http\Controllers\Transactions;

use App\Application\Transactions\Actions\CreateTransaction;
use App\Application\Transactions\Actions\ListTransactions;
use App\Application\Transactions\DTOs\CreateTransactionData;
use App\Application\Transactions\DTOs\TransactionFilterData;
use App\Domain\Transactions\Enums\TransactionType;
use App\Domain\Transactions\ValueObjects\Money;
use App\Domain\Transactions\ValueObjects\TransactionPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListTransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(ListTransactionsRequest $request, ListTransactions $list): AnonymousResourceCollection
    {
        $filters = $request->validated();

        return TransactionResource::collection($list->execute(
            $request->user()->id,
            new TransactionFilterData(
                isset($filters['type']) ? TransactionType::fromInput($filters['type']) : null,
                TransactionPeriod::fromStrings($filters['date_from'] ?? null, $filters['date_to'] ?? null),
                $filters['search'] ?? null,
                (int) ($filters['per_page'] ?? 20),
            ),
        ));
    }

    public function store(StoreTransactionRequest $request, CreateTransaction $create): JsonResponse
    {
        $data = $request->validated();
        $transaction = $create->execute(new CreateTransactionData(
            $request->user()->id,
            new \DateTimeImmutable($data['date']),
            trim($data['description']),
            Money::fromDecimal($data['amount']),
            TransactionType::fromInput($data['type']),
        ));

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }
}
