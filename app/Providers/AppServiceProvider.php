<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('is_admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('is_blogger', function (User $user) {
            return $user->role === 'blogger';
        });

        Gate::define('is_user', function (User $user) {
            return $user->role === 'blogger';
        });

        //registering policies
        Gate::policy(Post::class, PostPolicy::class);
    }
}
