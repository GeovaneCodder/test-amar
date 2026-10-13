<?php

namespace App\Infrastructure\Csv;

use App\Application\Imports\Contracts\CsvReader;
use App\Application\Imports\DTOs\CsvRow;
use App\Domain\Imports\Exceptions\ImportNotProcessableException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class NativeCsvReader implements CsvReader
{
    public function read(string $path): iterable
    {
        $stream = Storage::disk('local')->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to open the CSV file.');
        }

        try {
            $headers = fgetcsv($stream, escape: '');

            if ($headers === false) {
                throw new ImportNotProcessableException('The CSV file is empty.');
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(static fn ($header): string => mb_strtolower(trim((string) $header)), $headers);
            $required = ['date', 'description', 'amount', 'type'];

            if (count(array_unique($headers)) !== count($headers) || array_diff($required, $headers) !== []) {
                throw new ImportNotProcessableException('The header must contain date, description, amount, and type.');
            }

            $indexes = array_flip($headers);
            $line = 1;

            while (($columns = fgetcsv($stream, escape: '')) !== false) {
                $line++;

                if ($columns === [null] || (count($columns) === 1 && trim((string) $columns[0]) === '')) {
                    continue;
                }

                if (count($columns) !== count($headers)) {
                    yield new CsvRow($line, '', '', '', '', false);

                    continue;
                }

                yield new CsvRow(
                    $line,
                    trim((string) $columns[$indexes['date']]),
                    (string) $columns[$indexes['description']],
                    trim((string) $columns[$indexes['amount']]),
                    trim((string) $columns[$indexes['type']]),
                );
            }
        } finally {
            fclose($stream);
        }
    }
}
