<?php

namespace App\Http\Resources;

use App\Application\Transactions\DTOs\TransactionData;
use App\Domain\Transactions\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->resource instanceof TransactionData
                ? $this->resource->date->format('Y-m-d')
                : $this->transaction_date->format('Y-m-d'),
            'description' => $this->description,
            'amount' => $this->resource instanceof TransactionData
                ? Money::formatMinor($this->resource->amount->minorUnits)
                : Money::formatMinor($this->amount_minor),
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : $this->type,
            'created_at' => $this->resource instanceof TransactionData
                ? $this->createdAt?->format(DATE_ATOM)
                : ($this->created_at?->toISOString()),
        ];
    }
}
