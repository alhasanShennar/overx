<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EarningPeriod extends Model
{
    protected $fillable = [
        'client_id',
        'start_date',
        'end_date',
        'total_btc_earned',
        'average_btc_price',
        'total_revenue',
        'status',
        'client_decision',
        'requested_at',
        'processed_at',
        'is_locked',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_btc_earned' => 'decimal:8',
        'average_btc_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REQUEST_PENDING = 'request_pending';
    const STATUS_STORED = 'stored';
    const STATUS_CASHED_OUT = 'cashed_out';
    const STATUS_REJECTED = 'rejected';

    const DECISION_STORE = 'store';
    const DECISION_CASHOUT = 'cashout';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    public function cashout(): HasOne
    {
        return $this->hasOne(Cashout::class, 'transaction_id', 'id')
            ->through('transactions');
    }

    public function storedEarning(): HasOne
    {
        return $this->hasOne(StoredEarning::class);
    }

    public function isEligibleForRequest(): bool
    {
        return $this->status === self::STATUS_COMPLETED && ! $this->is_locked;
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function recalculateTotals(): void
    {
        $earnings = $this->earnings;

        $totalBtc = $earnings->sum('btc_earned');
        $totalRevenue = $earnings->sum('revenue');
        $avgPrice = $totalBtc > 0
            ? $earnings->sum(fn($e) => $e->btc_earned * $e->btc_price) / $totalBtc
            : 0;

        $this->update([
            'total_btc_earned' => $totalBtc,
            'average_btc_price' => $avgPrice,
            'total_revenue' => $totalRevenue,
        ]);
    }

    public function getDaysCountAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
