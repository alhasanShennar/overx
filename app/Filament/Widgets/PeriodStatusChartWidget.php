<?php

namespace App\Filament\Widgets;

use App\Models\EarningPeriod;
use Filament\Widgets\ChartWidget;

class PeriodStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Earning Periods by Status';
    protected static ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = EarningPeriod::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $map = [
            'pending'         => ['label' => 'Active',            'color' => '#6b7280'],
            'completed'       => ['label' => 'Completed',         'color' => '#3b82f6'],
            'request_pending' => ['label' => 'Awaiting Decision', 'color' => '#f59e0b'],
            'stored'          => ['label' => 'Stored',            'color' => '#10b981'],
            'cashed_out'      => ['label' => 'Cashed Out',        'color' => '#059669'],
            'rejected'        => ['label' => 'Rejected',          'color' => '#ef4444'],
        ];

        $labels   = [];
        $values   = [];
        $bgColors = [];

        foreach ($map as $key => $info) {
            $total = (int) ($counts[$key] ?? 0);
            if ($total > 0) {
                $labels[]   = $info['label'];
                $values[]   = $total;
                $bgColors[] = $info['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'data'            => $values,
                    'backgroundColor' => $bgColors,
                    'hoverOffset'     => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => ['position' => 'right'],
        ],
    ];
}
