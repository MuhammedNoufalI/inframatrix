<?php

namespace App\Policies;

use App\Models\GitProvider;
use App\Models\User;

class GitProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function view(User $user, GitProvider $gitProvider): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
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
