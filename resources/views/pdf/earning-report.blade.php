<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Earnings Report – {{ $client->user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

        /* ── Header ── */
        .header {
            background: #1a1a2e;
            color: #fff;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .brand { font-size: 20px; font-weight: 700; letter-spacing: 2px; color: #f59e0b; }
        .header .meta { font-size: 10px; color: #cbd5e1; text-align: right; line-height: 1.6; }

        /* ── Client info ── */
        .client-block {
            background: #f8fafc;
            border-left: 4px solid #f59e0b;
            padding: 12px 28px;
            margin: 16px 28px;
            border-radius: 4px;
        }
        .client-block .label { font-size: 9px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; margin-bottom: 2px; }
        .client-block .value { font-size: 13px; font-weight: 700; color: #1a1a2e; }

        /* ── Summary cards ── */
        .summary { display: flex; gap: 12px; margin: 0 28px 20px; }
        .card {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
        }
        .card .card-label { font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .card .card-value { font-size: 15px; font-weight: 700; margin-top: 4px; }
        .card.btc .card-value { color: #f59e0b; }
        .card.usd .card-value { color: #10b981; }
        .card.periods .card-value { color: #6366f1; }

        /* ── Period section ── */
        .period-block { margin: 0 28px 24px; }
        .period-header {
            background: #1e293b;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .period-header .period-name { font-size: 12px; font-weight: 700; }
        .period-header .period-badge {
            font-size: 9px;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending       { background: #64748b; color: #fff; }
        .badge-completed     { background: #6366f1; color: #fff; }
        .badge-cashed_out    { background: #10b981; color: #fff; }
        .badge-stored        { background: #3b82f6; color: #fff; }
        .badge-request_pending { background: #f59e0b; color: #fff; }
        .badge-rejected      { background: #ef4444; color: #fff; }

        .period-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 8px 14px;
            display: flex;
            gap: 24px;
        }
        .period-summary .ps-item .ps-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; }
        .period-summary .ps-item .ps-value { font-size: 12px; font-weight: 700; color: #1a1a2e; margin-top: 1px; }

        /* ── Earnings table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        thead tr { background: #f1f5f9; }
        thead th {
            padding: 7px 12px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.8px;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 6px 12px; font-size: 10px; color: #334155; }
        tbody td.right { text-align: right; font-family: monospace; }
        tbody td.btc-color { color: #f59e0b; font-weight: 600; }
        tbody td.usd-color { color: #10b981; font-weight: 600; }
        .no-earnings { padding: 12px; text-align: center; color: #94a3b8; font-style: italic; border: 1px solid #e2e8f0; border-top: none; }

        /* ── Footer ── */
        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            padding: 12px 28px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <div class="brand">OVERX</div>
        <div class="meta">
            Earnings Report<br>
            Generated: {{ now()->format('d M Y, H:i') }}<br>
            Client ID: #{{ $client->id }}
        </div>
    </div>

    {{-- ── Client info ── --}}
    <div class="client-block">
        <div class="label">Client Name</div>
        <div class="value">{{ $client->user->name }}</div>
    </div>

    {{-- ── Summary ── --}}
    <div class="summary">
        <div class="card periods">
            <div class="card-label">Total Periods</div>
            <div class="card-value">{{ $periodsCount }}</div>
        </div>
        <div class="card btc">
            <div class="card-label">Total BTC Earned</div>
            <div class="card-value">{{ number_format($totalBtc, 8) }}</div>
        </div>
        <div class="card usd">
            <div class="card-label">Total Revenue</div>
            <div class="card-value">${{ number_format($totalRevenue, 2) }}</div>
        </div>
    </div>

    {{-- ── Periods ── --}}
    @foreach ($periods as $period)
    <div class="period-block">

        <div class="period-header">
            <div class="period-name">{{ $period->period_label }}</div>
            <span class="period-badge badge-{{ $period->status }}">{{ str_replace('_', ' ', $period->status) }}</span>
        </div>

        <div class="period-summary">
            <div class="ps-item">
                <div class="ps-label">BTC Earned</div>
                <div class="ps-value">{{ number_format($period->total_btc_earned, 8) }} BTC</div>
            </div>
            <div class="ps-item">
                <div class="ps-label">Avg Price</div>
                <div class="ps-value">${{ number_format($period->average_btc_price, 2) }}</div>
            </div>
            <div class="ps-item">
                <div class="ps-label">Total Revenue</div>
                <div class="ps-value">${{ number_format($period->total_revenue, 2) }}</div>
            </div>
            <div class="ps-item">
                <div class="ps-label">Days</div>
                <div class="ps-value">{{ $period->earnings->count() }}</div>
            </div>
        </div>

        @if ($period->earnings->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="right">BTC Earned</th>
                    <th class="right">BTC Price</th>
                    <th class="right">Revenue (USD)</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($period->earnings->sortBy('date') as $earning)
                <tr>
                    <td>{{ $earning->date?->format('d M Y') }}</td>
                    <td class="right btc-color">{{ number_format($earning->btc_earned, 8) }}</td>
                    <td class="right">${{ number_format($earning->btc_price, 2) }}</td>
                    <td class="right usd-color">${{ number_format($earning->revenue, 2) }}</td>
                    <td>{{ $earning->additional_notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="no-earnings">No earnings recorded for this period.</div>
        @endif

    </div>
    @endforeach

    {{-- ── Footer ── --}}
    <div class="footer">
        This report is automatically generated by OverX platform &bull; Confidential
    </div>

</body>
</html>
