<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['id', 'category_id'];

    public function category()
    {
        return $this->hasOne(Category::class);
    } 
    
    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(CategoryTranslation::class)->where('locale', app()->getLocale());
    }
}
