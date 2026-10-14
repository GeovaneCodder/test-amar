<?php

namespace App\Http\Controllers\Auth;

use App\Application\Identity\Actions\LoginUserForSession;
use App\Application\Identity\Actions\RegisterUserForSession;
use App\Application\Identity\DTOs\LoginUserData;
use App\Application\Identity\DTOs\RegisterUserData;
use App\Application\Identity\DTOs\UserProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SessionAuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserForSession $register): JsonResponse
    {
        $data = $request->validated();
        $user = $register->execute(new RegisterUserData(
            $data['name'],
            $data['email'],
            $data['password'],
        ));

        return $this->startSession($request, $user, 201);
    }

    public function login(LoginRequest $request, LoginUserForSession $login): JsonResponse
    {
        $data = $request->validated();
        $user = $login->execute(new LoginUserData(
            $data['email'],
            $data['password'],
            'web-session',
        ));

        return $this->startSession($request, $user);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Session ended.']);
    }

    private function startSession(Request $request, UserProfileData $user, int $status = 200): JsonResponse
    {
        Auth::guard('web')->loginUsingId($user->id);
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'user' => $user->toArray(),
            ],
        ], $status);
    }
}
