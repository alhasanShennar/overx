<?php

namespace App\Filament\Widgets;

use App\Models\Earning;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AvgBtcPriceChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Avg BTC Price / Month';
    protected static string $color = 'info';
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
            return round((float) (Earning::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->avg('btc_price') ?? 0), 2);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'                => 'Avg BTC Price ($)',
                    'data'                 => $values,
                    'borderColor'          => '#6366f1',
                    'backgroundColor'      => 'rgba(99,102,241,0.12)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => '#6366f1',
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
            'y' => ['beginAtZero' => false],
        ],
    ];
}
