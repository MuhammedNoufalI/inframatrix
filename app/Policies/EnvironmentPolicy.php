<?php

namespace App\Policies;

use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, Environment $environment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $environment->project->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->wherePivotIn('role', ['owner', 'editor'])->exists();
    }

    public function update(User $user, Environment $environment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('infra_admin') && $environment->project->users()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return $environment->project->users()->wherePivotIn('role', ['owner', 'editor'])->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Environment $environment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $environment->project->users()->wherePivot('role', 'owner')->where('user_id', $user->id)->exists();
    }
}
