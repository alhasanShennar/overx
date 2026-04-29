<?php

namespace App\Services;

use App\Models\EarningPeriod;
use App\Models\StoredEarning;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class StoredEarningService
{
    /**
     * Process an approved store transaction.
     * Creates the StoredEarning record, completes the transaction, and locks the period.
     */
    public function process(Transaction $transaction, array $data = []): StoredEarning
    {
        return DB::transaction(function () use ($transaction, $data) {
            $storedEarning = StoredEarning::create([
                'client_id'         => $transaction->client_id,
                'transaction_id'    => $transaction->id,
                'earning_period_id' => $transaction->earning_period_id,
                'btc_amount'        => $transaction->btc_amount,
                'revenue_amount'    => $transaction->fiat_amount,
                'stored_at'         => now(),
                'notes'             => $data['notes'] ?? null,
            ]);

            $transaction->update([
                'status'       => Transaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->earningPeriod->update([
                'status'       => EarningPeriod::STATUS_STORED,
                'processed_at' => now(),
                'is_locked'    => true,
            ]);

            return $storedEarning;
        });
    }
}
