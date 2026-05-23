<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoredEarning extends Model
{
    protected $fillable = [
        'client_id',
        'transaction_id',
        'earning_period_id',
        'btc_amount',
        'revenue_amount',
        'stored_at',
        'notes',
    ];

    protected $casts = [
        'btc_amount' => 'decimal:8',
        'revenue_amount' => 'decimal:2',
        'stored_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function earningPeriod(): BelongsTo
    {
        return $this->belongsTo(EarningPeriod::class);
    }

    /**
     * Get the storing currency from the client's contract that covers this earning period.
     * Falls back to 'BTC' if no matching contract found.
     */
    public function getStoringCurrencyAttribute(): string
    {
        $period = $this->earningPeriod;

        if ($period) {
            $contract = $this->client->contracts()
                ->where('start_date', '<=', $period->start_date)
                ->where('end_date', '>=', $period->start_date)
                ->latest('start_date')
                ->first();
        } else {
            $contract = $this->client->contracts()->latest('start_date')->first();
        }

        return $contract?->storing_machines_currency ?? 'BTC';
    }

    /**
     * Get the stored amount formatted with its currency symbol.
     */
    public function getStoredAmountFormattedAttribute(): string
    {
        return $this->storingCurrency === 'BTC'
            ? number_format((float) $this->btc_amount, 8) . ' BTC'
            : '$' . number_format((float) $this->revenue_amount, 2) . ' USDT';
    }
}
