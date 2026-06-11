<?php

namespace App\Services;

use App\Models\TradingCashout;
use App\Models\TradingContract;
use App\Models\TradingEarning;
use App\Models\TradingPeriod;
use App\Models\TradingStoredEarning;
use App\Models\TradingTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TradingPeriodService
{
    public function getOrCreateMonthlyPeriod(TradingContract $contract, Carbon $date): TradingPeriod
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return TradingPeriod::firstOrCreate(
            [
                'trading_contract_id' => $contract->id,
                'year' => $date->year,
                'month' => $date->month,
            ],
            [
                'client_id' => $contract->client_id,
                'start_date' => $start,
                'end_date' => $end,
                'status' => TradingPeriod::STATUS_PENDING,
            ]
        );
    }

    public function refreshClientPeriods(int $clientId): void
    {
        TradingPeriod::where('client_id', $clientId)
            ->each(function (TradingPeriod $period) {
                $period->recalculateTotals();
                $period->maybeMarkCompleted();
            });
    }

    public function submitCashoutRequest(TradingPeriod $period, ?int $cashoutDetailsId = null): TradingTransaction
    {
        return DB::transaction(function () use ($period, $cashoutDetailsId) {
            $transaction = TradingTransaction::create([
                'client_id' => $period->client_id,
                'trading_contract_id' => $period->trading_contract_id,
                'trading_period_id' => $period->id,
                'type' => TradingTransaction::TYPE_CASHOUT,
                'amount' => $period->total_earning,
                'status' => TradingTransaction::STATUS_PENDING,
                'requested_by' => 'client',
                'requested_at' => now(),
            ]);

            TradingCashout::create([
                'client_id' => $period->client_id,
                'trading_transaction_id' => $transaction->id,
                'trading_period_id' => $period->id,
                'cashout_details_id' => $cashoutDetailsId,
                'amount' => $period->total_earning,
                'status' => 'pending',
            ]);

            $period->update([
                'status' => TradingPeriod::STATUS_REQUEST_PENDING,
                'client_decision' => TradingPeriod::DECISION_CASHOUT,
                'requested_at' => now(),
            ]);

            return $transaction;
        });
    }

    public function submitStoreRequest(TradingPeriod $period): TradingTransaction
    {
        return DB::transaction(function () use ($period) {
            $transaction = TradingTransaction::create([
                'client_id' => $period->client_id,
                'trading_contract_id' => $period->trading_contract_id,
                'trading_period_id' => $period->id,
                'type' => TradingTransaction::TYPE_STORE,
                'amount' => $period->total_earning,
                'status' => TradingTransaction::STATUS_PENDING,
                'requested_by' => 'client',
                'requested_at' => now(),
            ]);

            $period->update([
                'status' => TradingPeriod::STATUS_REQUEST_PENDING,
                'client_decision' => TradingPeriod::DECISION_STORE,
                'requested_at' => now(),
            ]);

            return $transaction;
        });
    }

    public function processCashout(TradingTransaction $transaction, array $data = []): TradingCashout
    {
        return DB::transaction(function () use ($transaction, $data) {
            $cashout = $transaction->tradingCashout ?? TradingCashout::create([
                'client_id' => $transaction->client_id,
                'trading_transaction_id' => $transaction->id,
                'trading_period_id' => $transaction->trading_period_id,
                'amount' => $transaction->amount,
                'status' => 'pending',
            ]);

            $cashout->update([
                'cashout_details_id' => $data['cashout_details_id'] ?? $cashout->cashout_details_id,
                'amount' => $data['amount'] ?? $transaction->amount,
                'receipt' => $data['receipt'] ?? $cashout->receipt,
                'date' => $data['date'] ?? today(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? $cashout->notes,
            ]);

            $transaction->update([
                'status' => TradingTransaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->tradingPeriod->update([
                'status' => TradingPeriod::STATUS_CASHED_OUT,
                'processed_at' => now(),
                'is_locked' => true,
            ]);

            return $cashout->fresh();
        });
    }

    public function processStore(TradingTransaction $transaction, array $data = []): TradingStoredEarning
    {
        return DB::transaction(function () use ($transaction, $data) {
            $stored = TradingStoredEarning::create([
                'client_id' => $transaction->client_id,
                'trading_transaction_id' => $transaction->id,
                'trading_period_id' => $transaction->trading_period_id,
                'trading_contract_id' => $transaction->trading_contract_id,
                'amount' => $transaction->amount,
                'stored_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $transaction->update([
                'status' => TradingTransaction::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);

            $transaction->tradingPeriod->update([
                'status' => TradingPeriod::STATUS_STORED,
                'processed_at' => now(),
                'is_locked' => true,
            ]);

            return $stored;
        });
    }

    public function rejectRequest(TradingTransaction $transaction, string $notes = ''): void
    {
        DB::transaction(function () use ($transaction, $notes) {
            $transaction->update([
                'status' => TradingTransaction::STATUS_REJECTED,
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            $transaction->tradingCashout?->update(['status' => 'cancelled']);

            $transaction->tradingPeriod->update([
                'status' => TradingPeriod::STATUS_COMPLETED,
                'client_decision' => null,
                'requested_at' => null,
            ]);
        });
    }
}
