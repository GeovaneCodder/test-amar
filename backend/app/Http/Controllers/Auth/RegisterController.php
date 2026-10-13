<?php

namespace App\Http\Controllers\Auth;

use App\Application\Identity\Actions\RegisterUser;
use App\Application\Identity\DTOs\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUser $register): JsonResponse
    {
        $data = $request->validated();
        $authenticatedUser = $register->execute(new RegisterUserData(
            $data['name'],
            $data['email'],
            $data['password'],
        ));

        return response()->json(['data' => $authenticatedUser->toArray()], 201);
    }
}
