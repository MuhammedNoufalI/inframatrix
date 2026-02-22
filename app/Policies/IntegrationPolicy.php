<?php

namespace App\Policies;

use App\Models\Integration;
use App\Models\User;

class IntegrationPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, Integration $integration): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $integration->environment->project->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->wherePivotIn('role', ['owner', 'editor'])->exists();
    }

    public function update(User $user, Integration $integration): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('infra_admin') && $integration->environment->project->users()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return $integration->environment->project->users()->wherePivotIn('role', ['owner', 'editor'])->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Integration $integration): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $integration->environment->project->users()->wherePivot('role', 'owner')->where('user_id', $user->id)->exists();
    }
}
