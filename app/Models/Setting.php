<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['logo', 'favicon', 'email', 'phone', 'facebook', 'twitter', 'instagram', 'linkedin'];

    public function translations()
    {
        return $this->hasMany(SettingTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(SettingTranslation::class)->where('locale', app()->getLocale());
    }
}
