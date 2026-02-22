<?php

namespace App\Policies;

use App\Models\Integration;
use App\Models\User;

class IntegrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function view(User $user, Integration $integration): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function update(User $user, Integration $integration): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function delete(User $user, Integration $integration): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }
}
