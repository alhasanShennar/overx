<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'client_id',
        'earning_period_id',
        'currency_id',
        'type',
        'btc_amount',
        'fiat_amount',
        'status',
        'requested_by',
        'requested_at',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'btc_amount' => 'decimal:8',
        'fiat_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    const TYPE_STORE = 'store';
    const TYPE_CASHOUT = 'cashout';
    const TYPE_ADJUSTMENT = 'adjustment';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function earningPeriod(): BelongsTo
    {
        return $this->belongsTo(EarningPeriod::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function cashout(): HasOne
    {
        return $this->hasOne(Cashout::class);
    }

    public function storedEarning(): HasOne
    {
        return $this->hasOne(StoredEarning::class);
    }
}
