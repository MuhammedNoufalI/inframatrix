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
            // Unconditionally force HTTPS scheme and root URL in production.
            // This prevents 405 Method Not Allowed errors on Livewire/Filament POST forms
            // caused by CloudPanel Varnish proxies losing the original request protocol.
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            if (config('app.url')) {
                \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            }

            // Force Livewire to use a dedicated update endpoint instead of the current URL.
            // This bypasses 405 errors caused by Varnish stripping custom Livewire headers
            // and Laravel failing to find a POST route for standard pages like /admin/login.
            \Livewire\Livewire::setUpdateRoute(function ($handle) {
                return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle);
            });
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
