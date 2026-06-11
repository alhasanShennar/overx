<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingStoredEarning extends Model
{
    protected $fillable = [
        'client_id',
        'trading_transaction_id',
        'trading_period_id',
        'trading_contract_id',
        'amount',
        'stored_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'stored_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingTransaction(): BelongsTo
    {
        return $this->belongsTo(TradingTransaction::class);
    }

    public function tradingPeriod(): BelongsTo
    {
        return $this->belongsTo(TradingPeriod::class);
    }

    public function tradingContract(): BelongsTo
    {
        return $this->belongsTo(TradingContract::class);
    }
}
