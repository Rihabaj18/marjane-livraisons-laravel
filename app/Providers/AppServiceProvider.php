<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('gerer-commandes', function ($user) {
            return in_array($user->role, ['responsable', 'admin']);
        });

        Gate::define('gerer-utilisateurs', function ($user) {
            return $user->role === 'admin';
        });
    }
}