<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Client\RequestTradingCashoutRequest;
use App\Http\Resources\TradingPeriodResource;
use App\Http\Resources\TradingTransactionResource;
use App\Models\TradingPeriod;
use App\Services\TradingPeriodService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradingPeriodController extends Controller
{
    use HttpResponses;

    public function __construct(private readonly TradingPeriodService $service) {}

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);

        $query = $client->tradingPeriods()
            ->with('tradingContract')
            ->orderByDesc('year')
            ->orderByDesc('month');

        if ($request->filled('trading_contract_id')) {
            $query->where('trading_contract_id', $request->integer('trading_contract_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->paginate(12);

        return $this->pagedSuccess(
            TradingPeriodResource::collection($periods),
            [
                'current_page' => $periods->currentPage(),
                'per_page' => $periods->perPage(),
                'total' => $periods->total(),
                'last_page' => $periods->lastPage(),
            ]
        );
    }

    public function pending(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);

        $periods = $client->tradingPeriods()
            ->with('tradingContract')
            ->where('status', TradingPeriod::STATUS_COMPLETED)
            ->orderByDesc('end_date')
            ->get()
            ->filter(fn (TradingPeriod $period) => $period->isEligibleForDecision());

        return $this->success(TradingPeriodResource::collection($periods->values()));
    }

    public function show(Request $request, TradingPeriod $tradingPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $tradingPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);
        $tradingPeriod->refresh();

        return $this->success(
            TradingPeriodResource::make(
                $tradingPeriod->load(['tradingContract', 'tradingEarnings', 'transaction'])
            )
        );
    }

    public function requestCashout(RequestTradingCashoutRequest $request, TradingPeriod $tradingPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $tradingPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);
        $tradingPeriod->refresh();

        if (! $tradingPeriod->isEligibleForDecision()) {
            return $this->error(null, 'This trading period is not eligible for a cashout request.', 422);
        }

        if ($request->filled('cashout_details_id')) {
            $ownsDetail = $client->cashoutDetails()
                ->where('id', $request->integer('cashout_details_id'))
                ->exists();

            if (! $ownsDetail) {
                return $this->error(null, 'Invalid cashout method.', 422);
            }
        }

        $transaction = $this->service->submitCashoutRequest(
            $tradingPeriod,
            $request->integer('cashout_details_id') ?: null
        );

        return $this->success(
            TradingTransactionResource::make($transaction->load('tradingPeriod')),
            'Trading cashout request submitted successfully.',
            201
        );
    }

    public function requestStore(Request $request, TradingPeriod $tradingPeriod): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $tradingPeriod->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);
        $tradingPeriod->refresh();

        if (! $tradingPeriod->isEligibleForDecision()) {
            return $this->error(null, 'This trading period is not eligible for a store request.', 422);
        }

        $transaction = $this->service->submitStoreRequest($tradingPeriod);

        return $this->success(
            TradingTransactionResource::make($transaction->load('tradingPeriod')),
            'Trading store request submitted successfully.',
            201
        );
    }

    public function chart(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $this->service->refreshClientPeriods($client->id);

        $periods = $client->tradingPeriods()
            ->orderBy('year')
            ->orderBy('month')
            ->get(['id', 'year', 'month', 'total_earning', 'status', 'start_date', 'end_date']);

        $data = $periods->map(fn (TradingPeriod $p) => [
            'label' => $p->period_label,
            'year' => $p->year,
            'month' => $p->month,
            'total_earning' => (float) $p->total_earning,
            'status' => $p->status,
            'start_date' => $p->start_date?->format('Y-m-d'),
            'end_date' => $p->end_date?->format('Y-m-d'),
        ]);

        return $this->success([
            'labels' => $data->pluck('label'),
            'earnings' => $data->pluck('total_earning'),
            'details' => $data,
        ]);
    }
}
