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
}
