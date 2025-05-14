<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['id'];

    public function category()
    {
        return $this->hasOne(Category::class);
    } 
    
    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translate($locale)
    {
        return $this->translations->where('locale', $locale)->first();
    }
}
