<?php

namespace App\Application\Identity\Contracts;

use App\Application\Identity\DTOs\AuthenticatedUserData;
use App\Application\Identity\DTOs\LoginUserData;
use App\Application\Identity\DTOs\RegisterUserData;

interface UserRepository
{
    public function register(RegisterUserData $data): AuthenticatedUserData;

    public function authenticate(LoginUserData $data): AuthenticatedUserData;

    public function revokeToken(int $userId, ?int $tokenId): void;
}
