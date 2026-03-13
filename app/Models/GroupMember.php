<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
  protected $fillable = [
    'group_id',
    'user_id',
    'role',
    'instrument',
    'joined_at',
    'is_active',
  ];

  protected $casts = [
    'joined_at' => 'date',
    'is_active' => 'boolean',
  ];

  public function group()
  {
    return $this->belongsTo(Group::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
