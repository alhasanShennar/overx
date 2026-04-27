<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function superAdmin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SuperAdmin::class);
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin()->exists();
    }

    public function isAdmin(): bool
    {
        return $this->admin()->exists() || $this->isSuperAdmin();
    }

    public function isClient(): bool
    {
        return $this->client()->exists();
    }
}
