<?php

namespace App\Events;

use App\Application\Imports\DTOs\ImportData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ImportFinished implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly ImportData $import,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'import.finished';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->import->id,
            'status' => $this->import->status->value,
            'total_rows' => $this->import->totalRows,
            'processed_rows' => $this->import->processedRows,
            'imported_rows' => $this->import->importedRows,
            'failed_rows' => $this->import->failedRows,
            'errors' => $this->import->errors,
            'created_at' => $this->import->createdAt?->format(DATE_ATOM),
        ];
    }
}
