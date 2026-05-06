<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\ChartWidget;

class RevenuePerClientChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'Total Revenue per Client';
    protected static ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $palette = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444',
            '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6',
        ];

        $clients  = Client::with('user')->get();
        $labels   = [];
        $values   = [];
        $bgColors = [];

        foreach ($clients as $i => $client) {
            $revenue = (float) $client->earnings()->sum('revenue');
            if ($revenue > 0) {
                $labels[]   = $client->user->name;
                $values[]   = round($revenue, 2);
                $bgColors[] = $palette[$i % count($palette)];
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
