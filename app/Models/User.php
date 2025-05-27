<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    const STATUS_BANNED = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'username',
        'password',
        'email',
        'status',
        'name',
        'phone',
        'address',
        'date_of_birth',
        'city_id',
        'district_id',
        'ward_id',
        'role_id',
        'avatar_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array 
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role() 
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Image::class, 'avatar_id');
    }

    public function hasPermission(string $permission)
    {
        if (!$this->role) {
            return false;
        }
        if ($this->role->name === 'admin') {
            return true;
        }
        return $this->role->permissions()->where('name', $permission)->exists();
    }
}
