<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Identity\DTOs\LoginUserData;
use App\Application\Identity\DTOs\UserProfileData;

final class LoginUserForSession
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(LoginUserData $data): UserProfileData
    {
        return $this->users->authenticateForSession($data);
    }
}
