<?php

namespace App\Filament\Widgets;

use App\Models\Cashout;
use App\Models\StoredEarning;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CashoutVsStoredChartWidget extends ChartWidget
{
    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Cashouts vs Stored Earnings (Last 6 Months)';
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $labels = $months->map(fn ($m) => $m->format('M Y'))->toArray();

        $cashouts = $months->map(function ($month) {
            return (float) Cashout::where('status', 'completed')
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
        })->toArray();

        $stored = $months->map(function ($month) {
            return (float) StoredEarning::whereYear('stored_at', $month->year)
                ->whereMonth('stored_at', $month->month)
                ->sum('revenue_amount');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Cashed Out ($)',
                    'data'            => $cashouts,
                    'backgroundColor' => 'rgba(239,68,68,0.75)',
                    'borderColor'     => '#ef4444',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Stored ($)',
                    'data'            => $stored,
                    'backgroundColor' => 'rgba(16,185,129,0.75)',
                    'borderColor'     => '#10b981',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => ['display' => true, 'position' => 'top'],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks'       => ['callback' => 'function(v){ return "$" + v.toLocaleString(); }'],
            ],
            'x' => ['grid' => ['display' => false]],
        ],
    ];
}
