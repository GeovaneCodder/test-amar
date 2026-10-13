<?php

namespace App\Http\Resources;

use App\Application\Imports\DTOs\ImportData;
use App\Domain\Imports\Enums\ImportStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->resource instanceof ImportData
                ? $this->status->value
                : ($this->status instanceof ImportStatus ? $this->status->value : $this->status),
            'total_rows' => $this->resource instanceof ImportData ? $this->totalRows : $this->total_rows,
            'processed_rows' => $this->resource instanceof ImportData ? $this->processedRows : $this->processed_rows,
            'imported_rows' => $this->resource instanceof ImportData ? $this->importedRows : $this->imported_rows,
            'failed_rows' => $this->resource instanceof ImportData ? $this->failedRows : $this->failed_rows,
            'errors' => $this->errors ?? [],
            'created_at' => $this->resource instanceof ImportData ? $this->createdAt : $this->created_at,
        ];
    }
}
