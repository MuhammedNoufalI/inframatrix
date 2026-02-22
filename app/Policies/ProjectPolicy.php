<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin', 'infra_user'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $project->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $project->users()->wherePivotIn('role', ['manager', 'editor'])->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $project->users()->wherePivot('role', 'manager')->where('user_id', $user->id)->exists();
    }
}
