<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarningResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $query = $client->earnings()->with('earningPeriod')->latest('date');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        if ($request->filled('earning_period_id')) {
            $query->where('earning_period_id', $request->earning_period_id);
        }

        $earnings = $query->paginate(30);

        return $this->pagedSuccess(
            EarningResource::collection($earnings),
            [
                'current_page' => $earnings->currentPage(),
                'per_page' => $earnings->perPage(),
                'total' => $earnings->total(),
                'last_page' => $earnings->lastPage(),
            ]
        );
    }
}
