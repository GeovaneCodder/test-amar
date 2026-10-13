<?php

namespace App\Http\Controllers\Auth;

use App\Application\Identity\Actions\LoginUser;
use App\Application\Identity\DTOs\LoginUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUser $login): JsonResponse
    {
        $data = $request->validated();
        $authenticatedUser = $login->execute(new LoginUserData(
            $data['email'],
            $data['password'],
            $data['device_name'] ?? 'api',
        ));

        return response()->json(['data' => $authenticatedUser->toArray()]);
    }
}
