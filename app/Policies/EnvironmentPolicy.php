<?php

namespace App\Policies;

use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin', 'infra_user'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, Environment $environment): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $environment->project->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function update(User $user, Environment $environment): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $environment->project->users()->wherePivotIn('role', ['manager', 'editor'])->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Environment $environment): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $environment->project->users()->wherePivot('role', 'manager')->where('user_id', $user->id)->exists();
    }
}
