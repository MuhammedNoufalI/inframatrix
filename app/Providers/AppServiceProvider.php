<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            if (config('app.url')) {
                \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            }
        }

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        \Illuminate\Support\Facades\Gate::policy(\App\Models\GitProvider::class, \App\Policies\GitProviderPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Server::class, \App\Policies\ServerPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\IntegrationType::class, \App\Policies\IntegrationTypePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\RecaptchaAccount::class, \App\Policies\RecaptchaAccountPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Project::class, \App\Policies\ProjectPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Environment::class, \App\Policies\EnvironmentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Invite::class, \App\Policies\InvitePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Integration::class, \App\Policies\IntegrationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Account::class, \App\Policies\AccountPolicy::class);
    }
}
