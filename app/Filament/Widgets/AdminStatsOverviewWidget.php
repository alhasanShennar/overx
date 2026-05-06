<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Contract;
use App\Models\EarningPeriod;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalClients     = Client::count();
        $storingMachines  = Contract::sum('storing_machines_no');
        $cashoutMachines  = Contract::sum('cashout_machines_no');
        $activePeriods    = EarningPeriod::where('status', EarningPeriod::STATUS_PENDING)->count();
        $awaitingDecision = EarningPeriod::where('status', EarningPeriod::STATUS_REQUEST_PENDING)->count();
        $pendingRequests  = Transaction::where('status', Transaction::STATUS_PENDING)->count();

        return [
            Stat::make('Total Clients', $totalClients)
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Storing Machines', $storingMachines)
                ->description("Cashout machines: {$cashoutMachines}")
                ->icon('heroicon-o-cpu-chip')
                ->color('info'),
            Stat::make('Active Periods', $activePeriods)
                ->description('Currently running')
                ->icon('heroicon-o-play-circle')
                ->color('primary'),
            Stat::make('Awaiting Decision', $awaitingDecision)
                ->description('Client requested — needs approval')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Pending Requests', $pendingRequests)
                ->description('Transactions to process')
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger'),
        ];
    }
}
