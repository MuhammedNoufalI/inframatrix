<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
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
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('infra_admin') && $project->users()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return $project->users()->wherePivotIn('role', ['owner', 'editor'])->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $project->users()->wherePivot('role', 'owner')->where('user_id', $user->id)->exists();
    }
}
