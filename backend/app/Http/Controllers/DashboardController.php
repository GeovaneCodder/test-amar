<?php

namespace App\Http\Controllers;

use App\Application\Dashboard\Actions\GetDashboardSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GetDashboardSummary $summary): JsonResponse
    {
        return response()->json([
            'data' => $summary->execute($request->user()->id)->toArray(),
        ]);
    }
}
