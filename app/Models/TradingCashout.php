<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingCashout extends Model
{
    protected $fillable = [
        'client_id',
        'trading_transaction_id',
        'trading_period_id',
        'cashout_details_id',
        'amount',
        'receipt',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
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

    public function cashoutDetail(): BelongsTo
    {
        return $this->belongsTo(CashoutDetail::class, 'cashout_details_id');
    }
}
