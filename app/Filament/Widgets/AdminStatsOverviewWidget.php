<?php

namespace App\Filament\Widgets;

use App\Models\Cashout;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Earning;
use App\Models\EarningPeriod;
use App\Models\StoredEarning;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalClients    = Client::count();
        $storingMachines = Contract::sum('storing_machines_no');
        $cashoutMachines = Contract::sum('cashout_machines_no');
        $totalBtc        = Earning::sum('btc_earned');
        $totalRevenue    = Earning::sum('revenue');
        $activePeriods   = EarningPeriod::where('status', EarningPeriod::STATUS_PENDING)->count();
        $awaitingDecision = EarningPeriod::where('status', EarningPeriod::STATUS_REQUEST_PENDING)->count();
        $pendingRequests = Transaction::where('status', Transaction::STATUS_PENDING)->count();
        $totalCashedOut  = Cashout::where('status', 'completed')->sum('amount');
        $totalStored     = StoredEarning::sum('revenue_amount');

        return [
            Stat::make('Total Clients', $totalClients)
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Storing Machines', $storingMachines)
                ->description("Cashout machines: {$cashoutMachines}")
                ->icon('heroicon-o-cpu-chip')
                ->color('info'),
            Stat::make('Total BTC Earned', number_format($totalBtc, 8) . ' BTC')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
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
            Stat::make('Total Cashed Out', '$' . number_format($totalCashedOut, 2))
                ->icon('heroicon-o-arrow-up-right')
                ->color('danger'),
            Stat::make('Total Stored', '$' . number_format($totalStored, 2))
                ->icon('heroicon-o-archive-box')
                ->color('success'),
        ];
    }
}
