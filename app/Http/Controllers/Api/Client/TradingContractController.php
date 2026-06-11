<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TradingContractResource;
use App\Models\TradingContract;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradingContractController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $query = $client->tradingContracts()
            ->withCount(['tradingPeriods', 'tradingEarnings'])
            ->orderByDesc('start_date');

        if ($request->get('status') === 'active') {
            $query->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()));
        }

        if ($request->get('status') === 'expired') {
            $query->whereNotNull('end_date')->where('end_date', '<', today());
        }

        $contracts = $query->paginate(15);

        return $this->pagedSuccess(
            TradingContractResource::collection($contracts),
            [
                'current_page' => $contracts->currentPage(),
                'per_page' => $contracts->perPage(),
                'total' => $contracts->total(),
                'last_page' => $contracts->lastPage(),
            ]
        );
    }

    public function show(Request $request, TradingContract $tradingContract): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $tradingContract->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $tradingContract->loadCount(['tradingPeriods', 'tradingEarnings']);

        return $this->success(TradingContractResource::make($tradingContract));
    }
}
