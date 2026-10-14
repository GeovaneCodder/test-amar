<?php

namespace App\Application\Identity\Contracts;

use App\Application\Identity\DTOs\AuthenticatedUserData;
use App\Application\Identity\DTOs\LoginUserData;
use App\Application\Identity\DTOs\RegisterUserData;
use App\Application\Identity\DTOs\UserProfileData;

interface UserRepository
{
    public function register(RegisterUserData $data): AuthenticatedUserData;

    public function authenticate(LoginUserData $data): AuthenticatedUserData;

    public function registerForSession(RegisterUserData $data): UserProfileData;

    public function authenticateForSession(LoginUserData $data): UserProfileData;

    public function revokeToken(int $userId, ?int $tokenId): void;
}
