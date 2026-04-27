<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoredEarningResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoredEarningController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $storedEarnings = $client->storedEarnings()
            ->with(['earningPeriod', 'transaction'])
            ->latest()
            ->paginate(15);

        $totalStoredBtc = $client->storedEarnings()->sum('btc_amount');
        $totalStoredRevenue = $client->storedEarnings()->sum('revenue_amount');

        return $this->pagedSuccess(
            StoredEarningResource::collection($storedEarnings),
            [
                'current_page' => $storedEarnings->currentPage(),
                'per_page' => $storedEarnings->perPage(),
                'total' => $storedEarnings->total(),
                'last_page' => $storedEarnings->lastPage(),
                'total_stored_btc' => $totalStoredBtc,
                'total_stored_revenue' => $totalStoredRevenue,
            ]
        );
    }
}
