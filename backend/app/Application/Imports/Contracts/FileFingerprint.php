<?php

namespace App\Application\Imports\Contracts;

interface FileFingerprint
{
    public function forFile(string $path): string;
}
