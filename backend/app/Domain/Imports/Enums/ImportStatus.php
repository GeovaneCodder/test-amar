<?php

namespace App\Domain\Imports\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';

    public function canProcess(): bool
    {
        return in_array($this, [self::Pending, self::Paused], true);
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Cancelled, self::Completed, self::CompletedWithErrors], true);
    }
}
