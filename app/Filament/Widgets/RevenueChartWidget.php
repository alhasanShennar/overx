<?php

namespace App\Filament\Widgets;

use App\Models\Earning;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Monthly Revenue';
    protected static string $color = 'success';
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
                ->sum('revenue');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'                => 'Revenue ($)',
                    'data'                 => $values,
                    'borderColor'          => '#10b981',
                    'backgroundColor'      => 'rgba(16,185,129,0.12)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => '#10b981',
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
                'ticks'       => ['callback' => 'function(v){ return "$" + v.toLocaleString(); }'],
            ],
        ],
    ];
}
