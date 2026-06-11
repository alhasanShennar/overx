<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TradingTransaction extends Model
{
    const TYPE_CASHOUT = 'cashout';

    const TYPE_STORE = 'store';

    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_REJECTED = 'rejected';

    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'client_id',
        'trading_contract_id',
        'trading_period_id',
        'type',
        'amount',
        'status',
        'requested_by',
        'requested_at',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingContract(): BelongsTo
    {
        return $this->belongsTo(TradingContract::class);
    }

    public function tradingPeriod(): BelongsTo
    {
        return $this->belongsTo(TradingPeriod::class);
    }

    public function tradingCashout(): HasOne
    {
        return $this->hasOne(TradingCashout::class);
    }

    public function tradingStoredEarning(): HasOne
    {
        return $this->hasOne(TradingStoredEarning::class);
    }
}
