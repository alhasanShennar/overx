<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Client\RequestCashoutRequest;
use App\Http\Requests\Api\Client\RequestStoreRequest;
use App\Http\Resources\EarningPeriodResource;
use App\Http\Resources\TransactionResource;
use App\Models\EarningPeriod;
use App\Services\EarningPeriodService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarningPeriodController extends Controller
{
    use HttpResponses;

    public function __construct(private readonly EarningPeriodService $service) {}

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $query = $client->earningPeriods()->with('transaction')->latest('start_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->paginate(12);

        return $this->pagedSuccess(
            EarningPeriodResource::collection($periods),
            [
                'current_page' => $periods->currentPage(),
                'per_page' => $periods->perPage(),
                'total' => $periods->total(),
                'last_page' => $periods->lastPage(),
            ]
        );
    }

    public function show(Request $request, EarningPeriod $earningPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $earningPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        return $this->success(
            EarningPeriodResource::make($earningPeriod->load(['earnings', 'transaction']))
        );
    }

    public function chart(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $periods = $client->earningPeriods()
            ->whereIn('status', [
                EarningPeriod::STATUS_STORED,
                EarningPeriod::STATUS_CASHED_OUT,
                EarningPeriod::STATUS_REQUEST_PENDING,
                EarningPeriod::STATUS_COMPLETED,
            ])
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'total_btc_earned', 'average_btc_price', 'total_revenue', 'status']);

        $data = $periods->map(fn ($p) => [
            'label'            => $p->start_date?->format('M Y'),
            'start_date'       => $p->start_date?->format('Y-m-d'),
            'end_date'         => $p->end_date?->format('Y-m-d'),
            'total_btc_earned' => (float) $p->total_btc_earned,
            'average_btc_price'=> (float) $p->average_btc_price,
            'total_revenue'    => (float) $p->total_revenue,
            'status'           => $p->status,
        ]);

        return $this->success([
            'labels'  => $data->pluck('label'),
            'revenue' => $data->pluck('total_revenue'),
            'btc'     => $data->pluck('total_btc_earned'),
            'details' => $data,
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $periods = $client->earningPeriods()
            ->where('status', EarningPeriod::STATUS_COMPLETED)
            ->latest('end_date')
            ->get();

        return $this->success(EarningPeriodResource::collection($periods));
    }

    public function requestCashout(RequestCashoutRequest $request, EarningPeriod $earningPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $earningPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        if (! $earningPeriod->isEligibleForRequest()) {
            return $this->error(null, 'This earning period is not eligible for a cashout request.', 422);
        }

        $transaction = $this->service->submitCashoutRequest($earningPeriod, 'client');

        return $this->success(
            TransactionResource::make($transaction->load('earningPeriod')),
            'Cashout request submitted successfully.',
            201
        );
    }

    public function requestStore(RequestStoreRequest $request, EarningPeriod $earningPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $earningPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        if (! $earningPeriod->isEligibleForRequest()) {
            return $this->error(null, 'This earning period is not eligible for a store request.', 422);
        }

        $transaction = $this->service->submitStoreRequest($earningPeriod, 'client');

        return $this->success(
            TransactionResource::make($transaction->load('earningPeriod')),
            'Store request submitted successfully.',
            201
        );
    }

    public function periodChart(Request $request, EarningPeriod $earningPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $earningPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $earningPeriod->load([
            'earnings:id,earning_period_id,date,btc_earned,btc_price,revenue,additional_notes',
            'transactions' => fn ($q) => $q->with(['cashout.cashoutDetail', 'storedEarning'])->latest(),
            'storedEarning',
        ]);

        // Daily earnings sorted by date
        $dailyEarnings = $earningPeriod->earnings
            ->sortBy('date')
            ->map(fn ($e) => [
                'date'             => $e->date?->format('Y-m-d'),
                'btc_earned'       => (float) $e->btc_earned,
                'btc_price'        => (float) $e->btc_price,
                'revenue'          => (float) $e->revenue,
                'additional_notes' => $e->additional_notes,
            ])->values();

        // Chart series: cumulative BTC and revenue per day
        $cumulativeBtc     = 0;
        $cumulativeRevenue = 0;
        $cumulative = $dailyEarnings->map(function ($day) use (&$cumulativeBtc, &$cumulativeRevenue) {
            $cumulativeBtc     += $day['btc_earned'];
            $cumulativeRevenue += $day['revenue'];
            return [
                'date'               => $day['date'],
                'cumulative_btc'     => round($cumulativeBtc, 8),
                'cumulative_revenue' => round($cumulativeRevenue, 2),
            ];
        });

        // All transactions on this period
        $transactions = $earningPeriod->transactions->map(function ($tx) {
            $cashout = null;
            if ($tx->cashout) {
                $cashout = [
                    'id'              => $tx->cashout->id,
                    'amount'          => (float) $tx->cashout->amount,
                    'btc_amount'      => (float) $tx->cashout->btc_amount,
                    'date'            => $tx->cashout->date?->format('Y-m-d'),
                    'status'          => $tx->cashout->status,
                    'receipt'         => $tx->cashout->receipt,
                    'notes'           => $tx->cashout->notes,
                    'cashout_details' => $tx->cashout->cashoutDetail ? [
                        'id'     => $tx->cashout->cashoutDetail->id,
                        'method' => $tx->cashout->cashoutDetail->method ?? null,
                        'info'   => $tx->cashout->cashoutDetail->info ?? null,
                    ] : null,
                ];
            }

            $stored = null;
            if ($tx->storedEarning) {
                $stored = [
                    'id'             => $tx->storedEarning->id,
                    'btc_amount'     => (float) $tx->storedEarning->btc_amount,
                    'revenue_amount' => (float) $tx->storedEarning->revenue_amount,
                    'stored_at'      => $tx->storedEarning->stored_at?->format('Y-m-d H:i:s'),
                    'notes'          => $tx->storedEarning->notes,
                ];
            }

            return [
                'id'           => $tx->id,
                'type'         => $tx->type,
                'btc_amount'   => (float) $tx->btc_amount,
                'fiat_amount'  => (float) $tx->fiat_amount,
                'status'       => $tx->status,
                'requested_by' => $tx->requested_by,
                'requested_at' => $tx->requested_at?->format('Y-m-d H:i:s'),
                'processed_at' => $tx->processed_at?->format('Y-m-d H:i:s'),
                'notes'        => $tx->notes,
                'cashout'      => $cashout,
                'stored'       => $stored,
            ];
        });

        // Stored earning directly on period
        $storedEarning = null;
        if ($earningPeriod->storedEarning) {
            $storedEarning = [
                'id'             => $earningPeriod->storedEarning->id,
                'btc_amount'     => (float) $earningPeriod->storedEarning->btc_amount,
                'revenue_amount' => (float) $earningPeriod->storedEarning->revenue_amount,
                'stored_at'      => $earningPeriod->storedEarning->stored_at?->format('Y-m-d H:i:s'),
                'notes'          => $earningPeriod->storedEarning->notes,
            ];
        }

        return $this->success([
            // Period info
            'period' => [
                'id'                => $earningPeriod->id,
                'start_date'        => $earningPeriod->start_date?->format('Y-m-d'),
                'end_date'          => $earningPeriod->end_date?->format('Y-m-d'),
                'days_count'        => $earningPeriod->days_count,
                'status'            => $earningPeriod->status,
                'client_decision'   => $earningPeriod->client_decision,
                'is_locked'         => $earningPeriod->is_locked,
                'is_eligible'       => $earningPeriod->isEligibleForRequest(),
                'requested_at'      => $earningPeriod->requested_at?->format('Y-m-d H:i:s'),
                'processed_at'      => $earningPeriod->processed_at?->format('Y-m-d H:i:s'),
                'notes'             => $earningPeriod->notes,
                'total_btc_earned'  => (float) $earningPeriod->total_btc_earned,
                'average_btc_price' => (float) $earningPeriod->average_btc_price,
                'total_revenue'     => (float) $earningPeriod->total_revenue,
            ],

            // Chart-ready data
            'chart' => [
                'labels'             => $dailyEarnings->pluck('date'),
                'daily_btc'          => $dailyEarnings->pluck('btc_earned'),
                'daily_revenue'      => $dailyEarnings->pluck('revenue'),
                'daily_btc_price'    => $dailyEarnings->pluck('btc_price'),
                'cumulative_btc'     => $cumulative->pluck('cumulative_btc'),
                'cumulative_revenue' => $cumulative->pluck('cumulative_revenue'),
            ],

            // Full daily breakdown
            'daily_earnings' => $dailyEarnings,

            // All transactions
            'transactions'   => $transactions,

            // Stored earning
            'stored_earning' => $storedEarning,
        ]);
    }
}
