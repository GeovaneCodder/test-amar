<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\Import;
use App\Infrastructure\Persistence\Eloquent\Models\User;

final class ImportPolicy
{
    public function view(User $user, Import $import): bool
    {
        return $import->user_id === $user->id;
    }

    public function update(User $user, Import $import): bool
    {
        return $this->view($user, $import);
    }

    public function delete(User $user, Import $import): bool
    {
        return $this->view($user, $import);
    }
}
