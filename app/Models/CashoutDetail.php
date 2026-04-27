<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashoutDetail extends Model
{
    protected $fillable = [
        'client_id',
        'label',
        'type',
        'crypto_wallet_address',
        'crypto_network',
        'account_holder',
        'swift_code',
        'routing_number',
        'bank_name',
        'iban',
        'currency_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function cashouts(): HasMany
    {
        return $this->hasMany(Cashout::class);
    }
}
