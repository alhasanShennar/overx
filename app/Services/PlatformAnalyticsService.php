<?php

namespace App\Services;

use App\Models\Cashout;
use App\Models\Earning;
use App\Models\StoredEarning;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    public function resolveRange(?string $dateFrom, ?string $dateTo): array
    {
        $to = filled($dateTo) ? Carbon::parse($dateTo)->endOfDay() : Carbon::today()->endOfDay();
        $from = filled($dateFrom)
            ? Carbon::parse($dateFrom)->startOfDay()
            : $to->copy()->subMonths(6)->startOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function suggestGroupBy(Carbon $from, Carbon $to): string
    {
        $days = $from->diffInDays($to) + 1;

        if ($days <= 45) {
            return 'daily';
        }

        if ($days <= 120) {
            return 'weekly';
        }

        return 'monthly';
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $earningQuery = Earning::query()->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        $totalRevenue = (float) (clone $earningQuery)->sum('revenue');
        $totalBtc = (float) (clone $earningQuery)->sum('btc_earned');
        $earningDays = (int) (clone $earningQuery)->distinct('date')->count('date');
        $daysInRange = max(1, $from->diffInDays($to) + 1);

        $cashouts = (float) Cashout::query()
            ->where('status', 'completed')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $stored = (float) StoredEarning::query()
            ->whereBetween('stored_at', [$from, $to])
            ->sum('revenue_amount');

        $activeClients = (int) (clone $earningQuery)->distinct('client_id')->count('client_id');

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_btc' => round($totalBtc, 8),
            'avg_daily_revenue' => round($totalRevenue / $daysInRange, 2),
            'avg_earning_day_revenue' => $earningDays > 0 ? round($totalRevenue / $earningDays, 2) : 0,
            'cashouts' => round($cashouts, 2),
            'stored' => round($stored, 2),
            'active_clients' => $activeClients,
            'earning_entries' => (int) (clone $earningQuery)->count(),
        ];
    }

    public function buildChartPayload(Carbon $from, Carbon $to, ?string $groupBy = null): array
    {
        $groupBy = $groupBy ?: $this->suggestGroupBy($from, $to);
        $buckets = $this->buildBuckets($from, $to, $groupBy);

        $revenueByBucket = Earning::query()
            ->select(
                DB::raw($this->bucketExpression('date', $groupBy) . ' as bucket'),
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(btc_earned) as total_btc')
            )
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('bucket')
            ->pluck('total_revenue', 'bucket')
            ->map(fn ($value) => (float) $value);

        $btcByBucket = Earning::query()
            ->select(
                DB::raw($this->bucketExpression('date', $groupBy) . ' as bucket'),
                DB::raw('SUM(btc_earned) as total_btc')
            )
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('bucket')
            ->pluck('total_btc', 'bucket')
            ->map(fn ($value) => (float) $value);

        $cashoutsByBucket = Cashout::query()
            ->select(
                DB::raw($this->bucketExpression('date', $groupBy) . ' as bucket'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('status', 'completed')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('bucket')
            ->pluck('total_amount', 'bucket')
            ->map(fn ($value) => (float) $value);

        $storedByBucket = StoredEarning::query()
            ->select(
                DB::raw($this->bucketExpression('stored_at', $groupBy) . ' as bucket'),
                DB::raw('SUM(revenue_amount) as total_amount')
            )
            ->whereBetween('stored_at', [$from, $to])
            ->groupBy('bucket')
            ->pluck('total_amount', 'bucket')
            ->map(fn ($value) => (float) $value);

        $labels = [];
        $revenue = [];
        $btc = [];
        $cumulative = [];
        $cashouts = [];
        $stored = [];
        $running = 0.0;

        foreach ($buckets as $bucket) {
            $labels[] = $bucket['label'];
            $value = round((float) ($revenueByBucket[$bucket['key']] ?? 0), 2);
            $revenue[] = $value;
            $btc[] = round((float) ($btcByBucket[$bucket['key']] ?? 0), 8);
            $running += $value;
            $cumulative[] = round($running, 2);
            $cashouts[] = round((float) ($cashoutsByBucket[$bucket['key']] ?? 0), 2);
            $stored[] = round((float) ($storedByBucket[$bucket['key']] ?? 0), 2);
        }

        $clients = Earning::query()
            ->select('client_id', DB::raw('SUM(revenue) as total_revenue'), DB::raw('SUM(btc_earned) as total_btc'))
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('client_id')
            ->orderByDesc('total_revenue')
            ->with('client.user')
            ->get()
            ->map(fn ($row) => [
                'name' => optional(optional($row->client)->user)->name ?? 'Unknown',
                'revenue' => round((float) $row->total_revenue, 2),
                'btc' => round((float) $row->total_btc, 8),
            ])
            ->values()
            ->all();

        return [
            'group_by' => $groupBy,
            'labels' => $labels,
            'revenue' => $revenue,
            'btc' => $btc,
            'cumulative' => $cumulative,
            'cashouts' => $cashouts,
            'stored' => $stored,
            'clients' => $clients,
        ];
    }

    /**
     * @return Collection<int, array{key: string, label: string}>
     */
    private function buildBuckets(Carbon $from, Carbon $to, string $groupBy): Collection
    {
        return match ($groupBy) {
            'weekly' => collect(CarbonPeriod::create($from->copy()->startOfWeek(), '1 week', $to))
                ->map(fn (Carbon $date) => [
                    'key' => $date->copy()->startOfWeek()->toDateString(),
                    'label' => 'Wk ' . $date->format('M j'),
                ])
                ->unique('key')
                ->values(),
            'monthly' => collect(CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to))
                ->map(fn (Carbon $date) => [
                    'key' => $date->format('Y-m'),
                    'label' => $date->format('M Y'),
                ])
                ->unique('key')
                ->values(),
            default => collect(CarbonPeriod::create($from, '1 day', $to))
                ->map(fn (Carbon $date) => [
                    'key' => $date->toDateString(),
                    'label' => $date->format('M j'),
                ])
                ->values(),
        };
    }

    private function bucketExpression(string $column, string $groupBy): string
    {
        return match ($groupBy) {
            'weekly' => "DATE(DATE_SUB({$column}, INTERVAL WEEKDAY({$column}) DAY))",
            'monthly' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "DATE({$column})",
        };
    }
}
