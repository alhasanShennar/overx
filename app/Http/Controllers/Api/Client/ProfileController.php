<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Client\UpdateProfileRequest;
use App\Http\Resources\ClientResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use HttpResponses;

    public function show(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        return $this->success(
            ClientResource::make($client->load(['user', 'contracts', 'cashoutDetails.currency']))
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user   = $request->user();
        $client = $user->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $userData   = $request->only(['name', 'email']);
        $clientData = $request->only(['phone', 'passport']);

        if (! empty($userData)) {
            $user->update($userData);
        }

        if (! empty($clientData)) {
            $client->update($clientData);
        }

        return $this->success(
            ClientResource::make($client->fresh()->load(['user', 'contracts', 'cashoutDetails.currency'])),
            'Profile updated successfully.'
        );
    }
}
