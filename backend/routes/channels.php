<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, string $userId): bool {
    return (string) $user->getAuthIdentifier() === $userId;
}, ['guards' => ['web']]);
