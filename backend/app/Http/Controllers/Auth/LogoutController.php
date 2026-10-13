<?php

namespace App\Http\Controllers\Auth;

use App\Application\Identity\Actions\LogoutUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutUser $logout): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $logout->execute(
            $request->user()->id,
            $token instanceof PersonalAccessToken ? $token->id : null,
        );

        return response()->json(['message' => 'Sessão encerrada.']);
    }
}
