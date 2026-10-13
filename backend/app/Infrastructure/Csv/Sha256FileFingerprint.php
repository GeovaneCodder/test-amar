<?php

namespace App\Infrastructure\Csv;

use App\Application\Imports\Contracts\FileFingerprint;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class Sha256FileFingerprint implements FileFingerprint
{
    public function forFile(string $path): string
    {
        $fingerprint = hash_file('sha256', Storage::disk('local')->path($path));

        if ($fingerprint === false) {
            throw new RuntimeException('Não foi possível calcular a assinatura do arquivo.');
        }

        return $fingerprint;
    }
}
