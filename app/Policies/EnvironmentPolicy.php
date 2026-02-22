<?php

namespace App\Policies;

use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function view(User $user, Environment $environment): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager']);
    }

    public function update(User $user, Environment $environment): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor']);
    }

    public function delete(User $user, Environment $environment): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }
}
