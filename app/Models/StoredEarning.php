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
}
