<?php

namespace App\Services;

use App\Models\Cashout;
use App\Models\Transaction;

class CashoutService
{
    /**
     * Ensure a pending cashout exists for approval workflow.
     *
     * @deprecated Use CashoutApprovalService for the multi-step approval flow.
     */
    public function process(Transaction $transaction, array $data): Cashout
    {
        $approvalService = app(CashoutApprovalService::class);
        $cashout = $approvalService->ensurePendingCashout($transaction);

        if ($approvalService->getNextApprovalLevel($cashout) === 3) {
            return $approvalService->approve($cashout, auth()->user(), $data);
        }

        return $cashout;
    }
}
