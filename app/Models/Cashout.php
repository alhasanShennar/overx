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
        'approved_1_by',
        'approved_1_at',
        'approved_2_by',
        'approved_2_at',
        'approved_3_by',
        'approved_3_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'btc_amount' => 'decimal:8',
        'approved_1_at' => 'datetime',
        'approved_2_at' => 'datetime',
        'approved_3_at' => 'datetime',
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

    public function approver1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_1_by');
    }

    public function approver2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_2_by');
    }

    public function approver3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_3_by');
    }
}
