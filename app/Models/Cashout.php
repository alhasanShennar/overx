<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cashout extends Model
{
    protected $fillable = [
        'client_id',
        'transaction_id',
        'cashout_details_id',
        'amount',
        'btc_amount',
        'receipt',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'btc_amount' => 'decimal:8',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function cashoutDetail(): BelongsTo
    {
        return $this->belongsTo(CashoutDetail::class, 'cashout_details_id');
    }
}
