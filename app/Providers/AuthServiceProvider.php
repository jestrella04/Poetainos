<?php

namespace App\Providers;

use App\Comment;
use App\Reply;
use App\User;
use App\Writing;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('update-writing', function(User $user, Writing $writing) {
            if ($writing->user->is($user) || isAdmin()) {
                return true;
            }
        });

        Gate::define('delete-writing', function(User $user, Writing $writing) {
            if ($writing->user->is($user) || isAdmin()) {
                return true;
            }
        });

        Gate::define('update-comment', function(User $user, Comment $comment) {
            if ($comment->user->is($user) || isAdmin() || isModerator()) {
                return true;
            }
        });

        Gate::define('delete-comment', function(User $user, Comment $comment) {
            if ($comment->user->is($user) || isAdmin() || isModerator()) {
                return true;
            }
        });

        Gate::define('update-reply', function(User $user, Reply $reply) {
            if ($reply->user->is($user) || isAdmin() || isModerator()) {
                return true;
            }
        });

        Gate::define('delete-reply', function(User $user, Reply $reply) {
            if ($reply->user->is($user) || isAdmin() || isModerator()) {
                return true;
            }
        });

        Gate::define('update-profile', function(User $user, User $profile) {
            if ($profile->is($user) || isAdmin()) {
                return true;
            }
        });

        Gate::define('delete-profile', function(User $user, User $profile) {
            if ($profile->is($user) || isAdmin()) {
                return true;
            }
        });
    }
}
