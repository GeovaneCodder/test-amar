<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Identity\DTOs\RegisterUserData;
use App\Application\Identity\DTOs\UserProfileData;

final class RegisterUserForSession
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(RegisterUserData $data): UserProfileData
    {
        return $this->users->registerForSession($data);
    }
}
