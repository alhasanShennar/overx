<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'passport',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function cashoutDetails(): HasMany
    {
        return $this->hasMany(CashoutDetail::class);
    }

    public function earningPeriods(): HasMany
    {
        return $this->hasMany(EarningPeriod::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashouts(): HasMany
    {
        return $this->hasMany(Cashout::class);
    }

    public function storedEarnings(): HasMany
    {
        return $this->hasMany(StoredEarning::class);
    }

    public function getCurrentStoringMachinesAttribute(): int
    {
        if ($this->relationLoaded('contracts')) {
            return (int) $this->contracts->sum('storing_machines_no');
        }
        return (int) $this->contracts()->sum('storing_machines_no');
    }

    public function getCurrentCashoutMachinesAttribute(): int
    {
        if ($this->relationLoaded('contracts')) {
            return (int) $this->contracts->sum('cashout_machines_no');
        }
        return (int) $this->contracts()->sum('cashout_machines_no');
    }

    public function getTotalMachinesAttribute(): int
    {
        return $this->current_storing_machines + $this->current_cashout_machines;
    }

    public function getStoredBalanceBtcAttribute(): float
    {
        return (float) $this->storedEarnings()->sum('btc_amount');
    }

    public function getStoredBalanceRevenueAttribute(): float
    {
        return (float) $this->storedEarnings()->sum('revenue_amount');
    }
}
