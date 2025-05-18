<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    
    public function update(User $user, Post $post): bool
    {
        return ($user->id === $post->category->user_id || $user->role === 'admin');
    }

    public function delete(User $user, Post $post): bool
    {
        return ($user->id === $post->category->user_id || $user->role === 'admin');
    }

}
