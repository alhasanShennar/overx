<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EarningPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function allPeriods(Request $request): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $client = Client::findOrFail($request->query('client'));

        $periods = $client->earningPeriods()
            ->with(['earnings' => fn ($q) => $q->orderBy('date')])
            ->latest('start_date')
            ->get();

        $pdf = Pdf::loadView('pdf.earning-report', [
            'client'       => $client->load('user'),
            'periods'      => $periods,
            'periodsCount' => $periods->count(),
            'totalBtc'     => $periods->sum('total_btc_earned'),
            'totalRevenue' => $periods->sum('total_revenue'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('earnings-report-' . now()->format('Y-m') . '.pdf');
    }

    public function singlePeriod(Request $request, EarningPeriod $earningPeriod): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $client = Client::findOrFail($request->query('client'));

        if ($earningPeriod->client_id !== $client->id) {
            abort(404);
        }

        $earningPeriod->load(['earnings' => fn ($q) => $q->orderBy('date')]);
        $periods = collect([$earningPeriod]);

        $pdf = Pdf::loadView('pdf.earning-report', [
            'client'       => $client->load('user'),
            'periods'      => $periods,
            'periodsCount' => 1,
            'totalBtc'     => $earningPeriod->total_btc_earned,
            'totalRevenue' => $earningPeriod->total_revenue,
        ])->setPaper('a4', 'portrait');

        $filename = 'earnings-report-' . str_replace(' ', '-', $earningPeriod->period_label) . '.pdf';

        return $pdf->stream($filename);
    }
}
