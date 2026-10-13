<?php

namespace App\Application\Imports\DTOs;

final readonly class StartImportData
{
    public function __construct(
        public int $userId,
        public string $filePath,
    ) {}
}
