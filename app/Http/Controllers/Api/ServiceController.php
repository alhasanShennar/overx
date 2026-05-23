<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceResource;
use App\Models\Service;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    use HttpResponses;

    public function index(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('order')
            ->get();

        return $this->success(ServiceResource::collection($services));
    }

    public function show(Service $service): JsonResponse
    {
        if (! $service->is_active) {
            return $this->error(null, 'Not found.', 404);
        }

        return $this->success(new ServiceResource($service));
    }
}
