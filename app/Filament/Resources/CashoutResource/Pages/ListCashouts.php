<?php

namespace App\Filament\Resources\CashoutResource\Pages;

use App\Filament\Resources\CashoutResource;
use App\Models\Transaction;
use App\Services\CashoutApprovalService;
use Filament\Resources\Pages\ListRecords;

class ListCashouts extends ListRecords
{
    protected static string $resource = CashoutResource::class;

    public function mount(): void
    {
        parent::mount();

        $approvalService = app(CashoutApprovalService::class);

        Transaction::query()
            ->where('type', Transaction::TYPE_CASHOUT)
            ->where('status', Transaction::STATUS_PENDING)
            ->whereDoesntHave('cashout')
            ->each(fn (Transaction $transaction) => $approvalService->ensurePendingCashout($transaction));
    }
}
