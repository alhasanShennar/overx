<?php

namespace App\Filament\Widgets;

use App\Models\Earning;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BtcEarnedChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Monthly BTC Earned';
    protected static string $color = 'warning';
    protected static ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $labels = $months->map(fn ($m) => $m->format('M Y'))->toArray();

        $values = $months->map(function ($month) {
            return (float) Earning::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('btc_earned');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'                => 'BTC Earned',
                    'data'                 => $values,
                    'borderColor'          => '#f59e0b',
                    'backgroundColor'      => 'rgba(245,158,11,0.12)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => ['display' => false],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks'       => ['callback' => 'function(v){ return v.toFixed(8) + " BTC"; }'],
            ],
        ],
    ];
}
