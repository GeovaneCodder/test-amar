<?php

namespace App\Application\Imports\Contracts;

use App\Application\Imports\DTOs\CsvRow;

interface CsvReader
{
    /** @return iterable<CsvRow> */
    public function read(string $path): iterable;
}
