<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    protected $fillable = ['id', 'post_id', 'title', 'content', 'locale'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
