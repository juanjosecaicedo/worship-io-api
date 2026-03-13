<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVocalProfile extends Model
{
    protected $fillable = [
        'user_id',
        'voice_type',
        'comfortable_key',
        'range_min',
        'range_max',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
