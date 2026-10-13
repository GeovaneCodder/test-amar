<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Identity\DTOs\AuthenticatedUserData;
use App\Application\Identity\DTOs\LoginUserData;
use App\Application\Identity\DTOs\RegisterUserData;
use App\Domain\Identity\Exceptions\InvalidCredentialsException;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentUserRepository implements UserRepository
{
    public function register(RegisterUserData $data): AuthenticatedUserData
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);

        return $this->createTokenData($user, 'api');
    }

    public function authenticate(LoginUserData $data): AuthenticatedUserData
    {
        $user = User::query()->where('email', $data->email)->first();

        if ($user === null || ! Hash::check($data->password, $user->password)) {
            throw new InvalidCredentialsException('The provided credentials are invalid.');
        }

        return $this->createTokenData($user, $data->deviceName);
    }

    public function revokeToken(int $userId, ?int $tokenId): void
    {
        if ($tokenId === null) {
            return;
        }

        User::query()
            ->whereKey($userId)
            ->firstOrFail()
            ->tokens()
            ->whereKey($tokenId)
            ->delete();
    }

    private function createTokenData(User $user, string $deviceName): AuthenticatedUserData
    {
        return new AuthenticatedUserData(
            $user->id,
            $user->name,
            $user->email,
            $user->createToken($deviceName)->plainTextToken,
        );
    }
}
