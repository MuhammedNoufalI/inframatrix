<?php

namespace App\Policies;

use App\Models\RecaptchaAccount;
use App\Models\User;

class RecaptchaAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function view(User $user, RecaptchaAccount $recaptchaAccount): bool
    {
        return $user->hasRole(['admin', 'infra_admin', 'manager', 'editor', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function update(User $user, RecaptchaAccount $recaptchaAccount): bool
    {
        return $user->hasRole(['admin', 'infra_admin']);
    }

    public function delete(User $user, RecaptchaAccount $recaptchaAccount): bool
    {
        return $user->hasRole(['admin']);
    }
}
