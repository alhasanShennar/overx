<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingEarning extends Model
{
    protected $fillable = [
        'client_id',
        'trading_contract_id',
        'date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $sync = function (TradingEarning $earning): void {
            $earning->tradingContract?->recalculateEarning();
        };

        static::saved($sync);
        static::deleted($sync);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tradingContract(): BelongsTo
    {
        return $this->belongsTo(TradingContract::class);
    }
}
