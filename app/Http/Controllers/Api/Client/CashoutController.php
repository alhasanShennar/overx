<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashoutResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashoutController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $cashouts = $client->cashouts()
            ->with(['cashoutDetail.currency', 'transaction'])
            ->latest()
            ->paginate(15);

        return $this->pagedSuccess(
            CashoutResource::collection($cashouts),
            [
                'current_page' => $cashouts->currentPage(),
                'per_page' => $cashouts->perPage(),
                'total' => $cashouts->total(),
                'last_page' => $cashouts->lastPage(),
            ]
        );
    }
}
