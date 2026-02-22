<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }
}
