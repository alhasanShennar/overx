<?php

namespace App\Services;

use App\Models\EarningPeriod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new pending transaction for an earning period.
     */
    public function createPending(
        EarningPeriod $period,
        string $type,
        string $requestedBy = 'client'
    ): Transaction {
        return Transaction::create([
            'client_id'         => $period->client_id,
            'earning_period_id' => $period->id,
            'type'              => $type,
            'btc_amount'        => $period->total_btc_earned,
            'fiat_amount'       => $period->total_revenue,
            'status'            => Transaction::STATUS_PENDING,
            'requested_by'      => $requestedBy,
            'requested_at'      => now(),
        ]);
    }

    /**
     * Get the existing pending transaction for a period/type, or create one (admin-initiated).
     */
    public function getOrCreatePending(
        EarningPeriod $period,
        string $type,
        string $requestedBy = 'admin'
    ): Transaction {
        $transaction = $period->transactions()
            ->where('type', $type)
            ->where('status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        return $transaction ?? $this->createPending($period, $type, $requestedBy);
    }

    /**
     * Reject a specific transaction and reset the earning period status.
     */
    public function reject(Transaction $transaction, string $notes = ''): void
    {
        DB::transaction(function () use ($transaction, $notes) {
            $transaction->update([
                'status'       => Transaction::STATUS_REJECTED,
                'processed_at' => now(),
                'notes'        => $notes,
            ]);

            $transaction->earningPeriod->update([
                'status'          => EarningPeriod::STATUS_REJECTED,
                'processed_at'    => now(),
                'client_decision' => null,
                'requested_at'    => null,
            ]);
        });
    }

    /**
     * Find the pending transaction for a period and reject it.
     * Falls back to directly rejecting the period if no transaction exists.
     */
    public function rejectForPeriod(EarningPeriod $period, string $notes = ''): void
    {
        $transaction = $period->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if ($transaction) {
            $this->reject($transaction, $notes);
            return;
        }

        // Fallback: no pending transaction found — reject the period directly
        $period->update([
            'status'       => EarningPeriod::STATUS_REJECTED,
            'processed_at' => now(),
        ]);
    }
}
