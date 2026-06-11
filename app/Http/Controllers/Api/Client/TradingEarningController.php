<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TradingEarningResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradingEarningController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $query = $client->tradingEarnings()
            ->with(['tradingContract', 'tradingPeriod'])
            ->orderByDesc('date');

        if ($request->filled('trading_contract_id')) {
            $query->where('trading_contract_id', $request->integer('trading_contract_id'));
        }

        if ($request->filled('trading_period_id')) {
            $query->where('trading_period_id', $request->integer('trading_period_id'));
        }

        if ($request->filled('year') && $request->filled('month')) {
            $query->whereYear('date', $request->integer('year'))
                ->whereMonth('date', $request->integer('month'));
        }

        $earnings = $query->paginate(20);

        return $this->pagedSuccess(
            TradingEarningResource::collection($earnings),
            [
                'current_page' => $earnings->currentPage(),
                'per_page' => $earnings->perPage(),
                'total' => $earnings->total(),
                'last_page' => $earnings->lastPage(),
            ]
        );
    }
}
