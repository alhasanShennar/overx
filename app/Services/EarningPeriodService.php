<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Earning;
use App\Models\EarningPeriod;
use App\Models\StoredEarning;
use App\Models\Transaction;
use App\Models\Cashout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EarningPeriodService
{
    /**
     * Get the active (pending) earning period for a client, or create a new one.
     * A new period starts from the day after the last period ended, or from today if none exist.
     */
    public function getOrCreateActivePeriod(Client $client): EarningPeriod
    {
        $activePeriod = EarningPeriod::where('client_id', $client->id)
            ->where('status', EarningPeriod::STATUS_PENDING)
            ->latest('start_date')
            ->first();

        if ($activePeriod) {
            return $activePeriod;
        }

        return $this->createNewPeriod($client);
    }

    /**
     * Create a new 30-day earning period for a client.
     */
    public function createNewPeriod(Client $client, ?Carbon $startDate = null): EarningPeriod
    {
        if (! $startDate) {
            $lastPeriod = EarningPeriod::where('client_id', $client->id)
                ->orderByDesc('end_date')
                ->first();

            $startDate = $lastPeriod
                ? $lastPeriod->end_date->addDay()
                : Carbon::today();
        }

        $endDate = $startDate->copy()->addDays(29); // 30 days inclusive

        return EarningPeriod::create([
            'client_id' => $client->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => EarningPeriod::STATUS_PENDING,
            'total_btc_earned' => 0,
            'average_btc_price' => 0,
            'total_revenue' => 0,
        ]);
    }

    /**
     * Resolve which earning period a given date belongs to for a client.
     * Creates the period if it doesn't exist yet.
     */
    public function resolveEarningPeriodForDate(Client $client, Carbon $date): EarningPeriod
    {
        $period = EarningPeriod::where('client_id', $client->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if ($period) {
            return $period;
        }

        // Find a pending period or create a new one aligned to the date
        $lastPeriod = EarningPeriod::where('client_id', $client->id)
            ->orderByDesc('end_date')
            ->first();

        if (! $lastPeriod) {
            // First period ever: start from the earning date
            return $this->createNewPeriod($client, $date->copy()->startOfDay());
        }

        // Create periods until we cover the given date
        $periodStart = $lastPeriod->end_date->addDay();
        while ($date->greaterThan($periodStart->copy()->addDays(29))) {
            $period = $this->createNewPeriod($client, $periodStart->copy());
            // Mark as completed since it has no earnings yet (gap period)
            $period->update(['status' => EarningPeriod::STATUS_COMPLETED]);
            $periodStart = $period->end_date->addDay();
        }

        return $this->createNewPeriod($client, $periodStart->copy());
    }

    /**
     * Check if a period has reached 30 days and auto-complete it.
     */
    public function maybeCompletePeriod(EarningPeriod $period): void
    {
        if ($period->status !== EarningPeriod::STATUS_PENDING) {
            return;
        }

        if (Carbon::today()->greaterThan($period->end_date)) {
            $period->update(['status' => EarningPeriod::STATUS_COMPLETED]);
        }
    }

    /**
     * Process a client cashout request for an earning period.
     */
    public function submitCashoutRequest(EarningPeriod $period, string $requestedBy = 'client'): Transaction
    {
        return DB::transaction(function () use ($period, $requestedBy) {
            $transaction = Transaction::create([
                'client_id' => $period->client_id,
                'earning_period_id' => $period->id,
                'type' => Transaction::TYPE_CASHOUT,
                'btc_amount' => $period->total_btc_earned,
                'fiat_amount' => $period->total_revenue,
                'status' => Transaction::STATUS_PENDING,
                'requested_by' => $requestedBy,
                'requested_at' => now(),
            ]);

            $period->update([
                'status' => EarningPeriod::STATUS_REQUEST_PENDING,
                'client_decision' => EarningPeriod::DECISION_CASHOUT,
                'requested_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Process a client store request for an earning period.
     */
    public function submitStoreRequest(EarningPeriod $period, string $requestedBy = 'client'): Transaction
    {
        return DB::transaction(function () use ($period, $requestedBy) {
            $transaction = Transaction::create([
                'client_id' => $period->client_id,
                'earning_period_id' => $period->id,
                'type' => Transaction::TYPE_STORE,
                'btc_amount' => $period->total_btc_earned,
                'fiat_amount' => $period->total_revenue,
                'status' => Transaction::STATUS_PENDING,
                'requested_by' => $requestedBy,
                'requested_at' => now(),
            ]);

            $period->update([
                'status' => EarningPeriod::STATUS_REQUEST_PENDING,
                'client_decision' => EarningPeriod::DECISION_STORE,
                'requested_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Admin approves and processes a cashout transaction.
     */
    public function processCashout(Transaction $transaction, array $data): Cashout
    {
        return DB::transaction(function () use ($transaction, $data) {
            $cashout = Cashout::create([
                'client_id' => $transaction->client_id,
                'transaction_id' => $transaction->id,
                'cashout_details_id' => $data['cashout_details_id'] ?? null,
                'amount' => $data['amount'] ?? $transaction->fiat_amount,
                'btc_amount' => $data['btc_amount'] ?? $transaction->btc_amount,
                'receipt' => $data['receipt'] ?? null,
                'date' => $data['date'] ?? today(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->earningPeriod->update([
                'status' => EarningPeriod::STATUS_CASHED_OUT,
                'processed_at' => now(),
                'is_locked' => true,
            ]);

            return $cashout;
        });
    }

    /**
     * Admin approves and processes a store transaction.
     */
    public function processStore(Transaction $transaction, array $data = []): StoredEarning
    {
        return DB::transaction(function () use ($transaction, $data) {
            $storedEarning = StoredEarning::create([
                'client_id' => $transaction->client_id,
                'transaction_id' => $transaction->id,
                'earning_period_id' => $transaction->earning_period_id,
                'btc_amount' => $transaction->btc_amount,
                'revenue_amount' => $transaction->fiat_amount,
                'stored_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->earningPeriod->update([
                'status' => EarningPeriod::STATUS_STORED,
                'processed_at' => now(),
                'is_locked' => true,
            ]);

            return $storedEarning;
        });
    }

    /**
     * Admin rejects a pending transaction request.
     */
    public function rejectRequest(Transaction $transaction, string $notes = ''): void
    {
        DB::transaction(function () use ($transaction, $notes) {
            $transaction->update([
                'status' => Transaction::STATUS_REJECTED,
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            $transaction->earningPeriod->update([
                'status' => EarningPeriod::STATUS_REJECTED,
                'processed_at' => now(),
                'client_decision' => null,
                'requested_at' => null,
            ]);
        });
    }
}
