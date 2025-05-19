<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    protected $fillable = ['title', 'content', 'locale', 'setting_id'];

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }
}
