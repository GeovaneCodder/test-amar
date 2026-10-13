<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Contracts\UserRepository;
use App\Application\Identity\DTOs\AuthenticatedUserData;
use App\Application\Identity\DTOs\LoginUserData;

final class LoginUser
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(LoginUserData $data): AuthenticatedUserData
    {
        return $this->users->authenticate($data);
    }
}
