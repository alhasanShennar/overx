<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cashout_approval_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'cashout_approval_level' => 'integer',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function hasCashoutApprovalLevel(): bool
    {
        return in_array($this->cashout_approval_level, [1, 2, 3], true);
    }

    public function cashoutApprovalLabel(): ?string
    {
        return match ($this->cashout_approval_level) {
            1 => 'Approve 1',
            2 => 'Approve 2',
            3 => 'Approve 3',
            default => null,
        };
    }

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

    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereDoesntHave('client');
    }
}
