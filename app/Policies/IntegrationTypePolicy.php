<?php

namespace App\Policies;

use App\Models\IntegrationType;
use App\Models\User;

class IntegrationTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function view(User $user, IntegrationType $integrationType): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
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
