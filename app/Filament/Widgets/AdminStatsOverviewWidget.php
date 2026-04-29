<?php

namespace App\Filament\Widgets;

use App\Models\Cashout;
use App\Models\Client;
use App\Models\Earning;
use App\Models\EarningPeriod;
use App\Models\StoredEarning;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdminStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalClients = Client::count();
        $totalMachines = \App\Models\Contract::sum('storing_machines_no') + \App\Models\Contract::sum('cashout_machines_no');
        $totalBtc = Earning::sum('btc_earned');
        $totalRevenue = Earning::sum('revenue');
        $pendingPeriods = EarningPeriod::where('status', EarningPeriod::STATUS_COMPLETED)->count();
        $pendingRequests = Transaction::where('status', 'pending')->count();
        $totalCashedOut = Cashout::where('status', 'completed')->sum('amount');
        $totalStored = StoredEarning::sum('revenue_amount');

        return [
            Stat::make('Total Clients', $totalClients)
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Total Machines', $totalMachines)
                ->icon('heroicon-o-cpu-chip')
                ->color('info'),
            Stat::make('Total BTC Earned', number_format($totalBtc, 8) . ' BTC')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Pending Periods', $pendingPeriods)
                ->description('Awaiting client decision')
                ->icon('heroicon-o-clock')
                ->color('gray'),
            Stat::make('Pending Requests', $pendingRequests)
                ->description('Cashout/Store requests to process')
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
