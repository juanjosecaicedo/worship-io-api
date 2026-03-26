<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'google_calendar_token',
        'google_id',
        'fcm_token',
        'is_active',
        'last_login_at',
        'password',
        'has_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_calendar_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'google_calendar_token' => 'array',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
            'has_password'      => 'boolean',
        ];
    }

    public function vocalProfile()
    {
        return $this->hasOne(UserVocalProfile::class);
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'instrument')
            ->withTimestamps();
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trialing'])
            ->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Preferencias del usuario
    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    // Obtener valor de una preferencia
    public function preference(string $key): ?string
    {
        return $this->preferences
            ->where('key', $key)
            ->first()
            ?->value ?? UserPreference::getDefault($key);
    }

    // Verificar si una preferencia es true
    public function preferenceEnabled(string $key): bool
    {
        return $this->preference($key) === 'true';
    }
}
