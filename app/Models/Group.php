<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'description',
        'color',
        'avatar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'instrument', 'joined_at', 'is_active')
            ->withTimestamps();
    }

    // ──────────────────── Helpers ───────────────────────

    /**
     * Check if a user is a member of the group.
     */
    public function hasMember(int $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if a user is an administrator or leader of the group.
     */
    public function isAdminOrLeader(int $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['admin', 'leader'])
            ->where('is_active', true)
            ->exists();
    }
}
