<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Contracts\UserRepository;

final class LogoutUser
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(int $userId, ?int $tokenId): void
    {
        $this->users->revokeToken($userId, $tokenId);
    }
}
