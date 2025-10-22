<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingLog extends Model
{
    protected $fillable = ['user_id', 'key', 'old_value', 'new_value'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}