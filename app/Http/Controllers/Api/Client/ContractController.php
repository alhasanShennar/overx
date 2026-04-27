<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $contracts = $client->contracts()->latest()->paginate(15);

        return $this->pagedSuccess(
            ContractResource::collection($contracts),
            [
                'current_page' => $contracts->currentPage(),
                'per_page' => $contracts->perPage(),
                'total' => $contracts->total(),
                'last_page' => $contracts->lastPage(),
            ]
        );
    }
}
