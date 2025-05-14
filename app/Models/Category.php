<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    } 
    
    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translate($locale)
    {
        return $this->translations->where('locale', $locale)->first();
    }
}
