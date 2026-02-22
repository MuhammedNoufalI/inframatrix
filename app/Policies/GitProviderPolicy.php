<?php

namespace App\Policies;

use App\Models\GitProvider;
use App\Models\User;

class GitProviderPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function view(User $user, GitProvider $gitProvider): bool
    {
        if ($user->hasRole(['admin', 'infra_admin'])) {
            return true;
        }
        return $user->projects()->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function update(User $user, GitProvider $gitProvider): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function delete(User $user, GitProvider $gitProvider): bool
    {
        return $user->hasRole(['admin']);
    }
}
