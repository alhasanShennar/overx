<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\EarningPeriodResource;
use App\Http\Resources\TransactionResource;
use App\Models\EarningPeriod;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $client = $request->user()->client;

        if (! $client) {
            return $this->error(null, 'Client profile not found.', 404);
        }

        $totalBtc = $client->earnings()->sum('btc_earned');
        $totalRevenue = $client->earnings()->sum('revenue');
        $storedBtc = $client->storedEarnings()->sum('btc_amount');
        $storedRevenue = $client->storedEarnings()->sum('revenue_amount');
        $cashedOutRevenue = $client->cashouts()->where('status', 'completed')->sum('amount');

        $pendingPeriods = EarningPeriod::where('client_id', $client->id)
            ->where('status', EarningPeriod::STATUS_COMPLETED)
            ->count();

        $pendingRequests = $client->transactions()
            ->where('status', 'pending')
            ->count();

        return $this->success([
            'client' => ClientResource::make($client->load('user')),
            'stats' => [
                'total_btc_earned' => $totalBtc,
                'total_revenue' => $totalRevenue,
                'stored_balance_btc' => $storedBtc,
                'stored_balance_revenue' => $storedRevenue,
                'total_cashed_out' => $cashedOutRevenue,
                'pending_periods' => $pendingPeriods,
                'pending_requests' => $pendingRequests,
                'total_machines' => $client->current_storing_machines + $client->current_cashout_machines,
                'storing_machines' => $client->current_storing_machines,
                'cashout_machines' => $client->current_cashout_machines,
            ],
        ]);
    }
}
