<?php

namespace App\Http\Controllers\Imports;

use App\Application\Imports\Actions\CancelImport;
use App\Application\Imports\Actions\PauseImport;
use App\Application\Imports\Actions\ResumeImport;
use App\Application\Imports\Actions\StartImport;
use App\Application\Imports\Contracts\ImportRepository;
use App\Application\Imports\DTOs\StartImportData;
use App\Domain\Imports\Exceptions\ImportNotProcessableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function store(StoreImportRequest $request, StartImport $start): JsonResponse
    {
        $storedPath = $request->file('file')->store('imports/'.$request->user()->id, 'local');
        $import = $start->execute(new StartImportData(
            $request->user()->id,
            $storedPath,
        ));

        if ($import->filePath !== $storedPath) {
            Storage::disk('local')->delete($storedPath);
        }

        return (new ImportResource($import))
            ->response()
            ->setStatusCode(202);
    }

    public function show(Request $request, int $import, ImportRepository $imports): ImportResource
    {
        $record = $imports->find($import);

        abort_if($record === null || $record->userId !== $request->user()->id, 404);

        return new ImportResource($record);
    }

    public function pause(Request $request, int $import, PauseImport $pause): JsonResponse
    {
        $this->runTransition(fn () => $pause->execute($import, $request->user()->id));

        return response()->json(['message' => 'Importação pausada.']);
    }

    public function resume(Request $request, int $import, ResumeImport $resume): JsonResponse
    {
        $this->runTransition(fn () => $resume->execute($import, $request->user()->id));

        return response()->json(['message' => 'Importação retomada.']);
    }

    public function cancel(Request $request, int $import, CancelImport $cancel): JsonResponse
    {
        $this->runTransition(fn () => $cancel->execute($import, $request->user()->id));

        return response()->json(['message' => 'Importação cancelada.']);
    }

    private function runTransition(callable $transition): void
    {
        try {
            $transition();
        } catch (ImportNotProcessableException $exception) {
            abort(409, $exception->getMessage());
        }
    }
}
