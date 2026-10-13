<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Identity\DTOs\AuthenticatedUserData;
use App\Application\Identity\DTOs\RegisterUserData;

final class RegisterUser
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(RegisterUserData $data): AuthenticatedUserData
    {
        return $this->users->register($data);
    }
}
