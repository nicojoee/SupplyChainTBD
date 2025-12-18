<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    public function factory()
    {
        return $this->hasOne(Factory::class);
    }

    public function distributor()
    {
        return $this->hasOne(Distributor::class);
    }

    public function courier()
    {
        return $this->hasOne(Courier::class);
    }
}
