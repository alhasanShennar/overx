<?php

namespace App\Services;

use App\Models\Cashout;
use App\Models\EarningPeriod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CashoutService
{
    /**
     * Process an approved cashout transaction.
     * Creates the Cashout record, completes the transaction, and locks the period.
     */
    public function process(Transaction $transaction, array $data): Cashout
    {
        return DB::transaction(function () use ($transaction, $data) {
            $cashout = Cashout::create([
                'client_id'          => $transaction->client_id,
                'transaction_id'     => $transaction->id,
                'cashout_details_id' => $data['cashout_details_id'] ?? null,
                'amount'             => $data['amount'] ?? $transaction->fiat_amount,
                'btc_amount'         => $data['btc_amount'] ?? $transaction->btc_amount,
                'receipt'            => $data['receipt'] ?? null,
                'date'               => $data['date'] ?? today(),
                'status'             => 'completed',
                'notes'              => $data['notes'] ?? null,
            ]);

            $transaction->update([
                'status'       => Transaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->earningPeriod->update([
                'status'       => EarningPeriod::STATUS_CASHED_OUT,
                'processed_at' => now(),
                'is_locked'    => true,
            ]);

            return $cashout;
        });
    }
}
