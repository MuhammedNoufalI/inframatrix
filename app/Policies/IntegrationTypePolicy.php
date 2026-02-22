<?php

namespace App\Policies;

use App\Models\IntegrationType;
use App\Models\User;

class IntegrationTypePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, IntegrationType $integrationType): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin']);
    }

    public function update(User $user, IntegrationType $integrationType): bool
    {
        return $user->hasRole(['admin']);
    }

    public function delete(User $user, IntegrationType $integrationType): bool
    {
        return $user->hasRole(['admin']);
    }
}
