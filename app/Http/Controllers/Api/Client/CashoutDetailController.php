<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Client\StoreCashoutDetailRequest;
use App\Http\Requests\Api\Client\UpdateCashoutDetailRequest;
use App\Http\Resources\CashoutDetailResource;
use App\Models\CashoutDetail;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashoutDetailController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $details = $client->cashoutDetails()->with('currency')->get();

        return $this->success(CashoutDetailResource::collection($details));
    }

    public function store(StoreCashoutDetailRequest $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $data = $request->validated();

        // If setting as default, unset existing default
        if (! empty($data['is_default'])) {
            $client->cashoutDetails()->where('is_default', true)->update(['is_default' => false]);
        }

        $detail = $client->cashoutDetails()->create($data);

        return $this->success(
            CashoutDetailResource::make($detail->load('currency')),
            'Cashout detail created successfully.',
            201
        );
    }

    public function update(UpdateCashoutDetailRequest $request, CashoutDetail $cashoutDetail): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $cashoutDetail->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $data = $request->validated();

        if (! empty($data['is_default'])) {
            $client->cashoutDetails()->where('is_default', true)->update(['is_default' => false]);
        }

        $cashoutDetail->update($data);

        return $this->success(
            CashoutDetailResource::make($cashoutDetail->fresh()->load('currency')),
            'Cashout detail updated successfully.'
        );
    }

    public function destroy(Request $request, CashoutDetail $cashoutDetail): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client || $client->id !== $cashoutDetail->client_id) {
            return $this->error(null, 'Not found.', 404);
        }

        $cashoutDetail->delete();

        return $this->success(null, 'Cashout detail deleted successfully.');
    }
}
