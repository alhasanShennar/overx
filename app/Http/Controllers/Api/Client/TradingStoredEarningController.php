<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TradingStoredEarningResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradingStoredEarningController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $stored = $client->tradingStoredEarnings()
            ->with(['tradingContract', 'tradingPeriod'])
            ->orderByDesc('stored_at')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => null,
            'data' => TradingStoredEarningResource::collection($stored),
            'meta' => [
                'current_page' => $stored->currentPage(),
                'per_page' => $stored->perPage(),
                'total' => $stored->total(),
                'last_page' => $stored->lastPage(),
                'stored_balance' => (float) $client->trading_stored_balance,
            ],
        ]);
    }

    public function balance(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        return $this->success([
            'stored_balance' => (float) $client->trading_stored_balance,
        ]);
    }
}
