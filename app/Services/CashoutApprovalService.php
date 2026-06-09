<?php

namespace App\Services;

use App\Models\Cashout;
use App\Models\EarningPeriod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CashoutApprovalService
{
    public function ensurePendingCashout(Transaction $transaction): Cashout
    {
        return Cashout::firstOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'client_id' => $transaction->client_id,
                'amount' => $transaction->fiat_amount,
                'btc_amount' => $transaction->btc_amount,
                'status' => 'pending',
            ]
        );
    }

    public function getNextApprovalLevel(Cashout $cashout): ?int
    {
        if ($cashout->status !== 'pending') {
            return null;
        }

        if (! $cashout->approved_1_at) {
            return 1;
        }

        if (! $cashout->approved_2_at) {
            return 2;
        }

        if (! $cashout->approved_3_at) {
            return 3;
        }

        return null;
    }

    public function canUserApprove(Cashout $cashout, User $user): bool
    {
        $nextLevel = $this->getNextApprovalLevel($cashout);

        if ($nextLevel === null) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $user->cashout_approval_level === $nextLevel;
    }

    public function approve(Cashout $cashout, User $user, array $data = []): Cashout
    {
        $nextLevel = $this->getNextApprovalLevel($cashout);

        if ($nextLevel === null) {
            throw new AuthorizationException('This cashout has already been fully approved.');
        }

        if (! $this->canUserApprove($cashout, $user)) {
            throw new AuthorizationException("You are not authorized to perform Approve {$nextLevel}.");
        }

        return DB::transaction(function () use ($cashout, $user, $nextLevel, $data) {
            $cashout->update([
                "approved_{$nextLevel}_by" => $user->id,
                "approved_{$nextLevel}_at" => now(),
            ]);

            if ($nextLevel === 3) {
                return $this->finalize($cashout->fresh(), $data);
            }

            return $cashout->fresh();
        });
    }

    public function finalize(Cashout $cashout, array $data = []): Cashout
    {
        $transaction = $cashout->transaction;

        $cashout->update([
            'cashout_details_id' => $data['cashout_details_id'] ?? $cashout->cashout_details_id,
            'amount' => $data['amount'] ?? $cashout->amount ?? $transaction->fiat_amount,
            'btc_amount' => $data['btc_amount'] ?? $cashout->btc_amount ?? $transaction->btc_amount,
            'receipt' => $data['receipt'] ?? $cashout->receipt,
            'date' => $data['date'] ?? $cashout->date ?? today(),
            'notes' => $data['notes'] ?? $cashout->notes,
            'status' => 'completed',
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

        return $cashout->fresh();
    }

    public function reject(Cashout $cashout, string $notes = ''): void
    {
        DB::transaction(function () use ($cashout, $notes) {
            $transaction = $cashout->transaction;

            $cashout->update([
                'status' => 'cancelled',
                'notes' => $notes ?: $cashout->notes,
            ]);

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
