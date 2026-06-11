<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TradingCashoutResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradingCashoutController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $cashouts = $client->tradingCashouts()
            ->with('cashoutDetail')
            ->orderByDesc('date')
            ->paginate(15);

        return $this->pagedSuccess(
            TradingCashoutResource::collection($cashouts),
            [
                'current_page' => $cashouts->currentPage(),
                'per_page' => $cashouts->perPage(),
                'total' => $cashouts->total(),
                'last_page' => $cashouts->lastPage(),
            ]
        );
    }
}
