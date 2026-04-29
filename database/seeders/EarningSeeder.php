<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Earning;
use App\Models\EarningPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EarningSeeder extends Seeder
{
    public function run(): void
    {
        if (Earning::exists()) {
            $this->command->info('EarningSeeder: earnings already exist, skipping.');
            return;
        }

        $ahmed    = Client::whereHas('user', fn($q) => $q->where('email', 'ahmed@client.com'))->firstOrFail();
        $mohammed = Client::whereHas('user', fn($q) => $q->where('email', 'mohammed@client.com'))->firstOrFail();
        $sara     = Client::whereHas('user', fn($q) => $q->where('email', 'sara@client.com'))->firstOrFail();

        // Helper: fetch period by client and start date
        $period = fn(int $clientId, string $start) =>
            EarningPeriod::where('client_id', $clientId)->where('start_date', $start)->firstOrFail();

        $ahmedP1 = $period($ahmed->id, '2026-01-01');
        $ahmedP2 = $period($ahmed->id, '2026-01-31');
        $ahmedP3 = $period($ahmed->id, '2026-03-02');

        $mohammedP1 = $period($mohammed->id, '2026-02-01');
        $mohammedP2 = $period($mohammed->id, '2026-03-03');

        $saraP1 = $period($sara->id, '2026-01-15');
        $saraP2 = $period($sara->id, '2026-02-14');
        $saraP3 = $period($sara->id, '2026-03-16');

        /*
         * Earnings are stored WITHOUT triggering the Earning::booted() observers
         * (which would recalculate the period totals on every row insert).
         * We insert raw then call recalculateTotals() once per period at the end.
         *
         * BTC price range used: ~$85,000 – $95,000 (April 2026 estimates).
         * BTC earned per machine per day ≈ 0.00028 BTC.
         */
        $earnings = [];

        // ── Ahmed – Period 1 (Jan 01–30, 5 machines) ─────────────────────
        foreach (range(1, 30) as $day) {
            $earnings[] = [
                'client_id'        => $ahmed->id,
                'earning_period_id'=> $ahmedP1->id,
                'date'             => sprintf('2026-01-%02d', $day),
                'btc_earned'       => 0.00140, // 5 machines × 0.00028
                'btc_price'        => 87000 + ($day * 100),
                'revenue'          => 0.00140 * (87000 + ($day * 100)),
                'additional_notes' => null,
            ];
        }

        // ── Ahmed – Period 2 (Jan 31–Mar 01, 5 machines) ─────────────────
        $p2Dates = array_merge(
            ['2026-01-31'],
            array_map(fn($d) => sprintf('2026-02-%02d', $d), range(1, 28)),
            ['2026-03-01']
        );
        foreach ($p2Dates as $i => $date) {
            $earnings[] = [
                'client_id'        => $ahmed->id,
                'earning_period_id'=> $ahmedP2->id,
                'date'             => $date,
                'btc_earned'       => 0.00140,
                'btc_price'        => 89000 + ($i * 80),
                'revenue'          => 0.00140 * (89000 + ($i * 80)),
                'additional_notes' => null,
            ];
        }

        // ── Ahmed – Period 3 (Mar 02–Mar 31, active — partial, 20 days) ──
        foreach (range(2, 21) as $day) {
            $earnings[] = [
                'client_id'        => $ahmed->id,
                'earning_period_id'=> $ahmedP3->id,
                'date'             => sprintf('2026-03-%02d', $day),
                'btc_earned'       => 0.00140,
                'btc_price'        => 91000 + ($day * 50),
                'revenue'          => 0.00140 * (91000 + ($day * 50)),
                'additional_notes' => null,
            ];
        }

        // ── Mohammed – Period 1 (Feb 01–Mar 02, 3 machines) ─────────────
        $moP1Dates = array_merge(
            array_map(fn($d) => sprintf('2026-02-%02d', $d), range(1, 28)),
            ['2026-03-01', '2026-03-02']
        );
        foreach ($moP1Dates as $i => $date) {
            $earnings[] = [
                'client_id'        => $mohammed->id,
                'earning_period_id'=> $mohammedP1->id,
                'date'             => $date,
                'btc_earned'       => 0.00084, // 3 machines × 0.00028
                'btc_price'        => 88000 + ($i * 90),
                'revenue'          => 0.00084 * (88000 + ($i * 90)),
                'additional_notes' => null,
            ];
        }

        // ── Mohammed – Period 2 (Mar 03–Apr 01, completed, all 30 days) ──
        $moP2Dates = array_merge(
            array_map(fn($d) => sprintf('2026-03-%02d', $d), range(3, 31)),
            ['2026-04-01']
        );
        foreach ($moP2Dates as $i => $date) {
            $earnings[] = [
                'client_id'        => $mohammed->id,
                'earning_period_id'=> $mohammedP2->id,
                'date'             => $date,
                'btc_earned'       => 0.00084,
                'btc_price'        => 90000 + ($i * 70),
                'revenue'          => 0.00084 * (90000 + ($i * 70)),
                'additional_notes' => null,
            ];
        }

        // ── Sara – Period 1 (Jan 15–Feb 13, 7 machines) ─────────────────
        $saraP1Dates = array_merge(
            array_map(fn($d) => sprintf('2026-01-%02d', $d), range(15, 31)),
            array_map(fn($d) => sprintf('2026-02-%02d', $d), range(1, 13))
        );
        foreach ($saraP1Dates as $i => $date) {
            $earnings[] = [
                'client_id'        => $sara->id,
                'earning_period_id'=> $saraP1->id,
                'date'             => $date,
                'btc_earned'       => 0.00196, // 7 machines × 0.00028
                'btc_price'        => 86000 + ($i * 120),
                'revenue'          => 0.00196 * (86000 + ($i * 120)),
                'additional_notes' => null,
            ];
        }

        // ── Sara – Period 2 (Feb 14–Mar 15, request_pending cashout) ─────
        $saraP2Dates = array_merge(
            array_map(fn($d) => sprintf('2026-02-%02d', $d), range(14, 28)),
            array_map(fn($d) => sprintf('2026-03-%02d', $d), range(1, 15))
        );
        foreach ($saraP2Dates as $i => $date) {
            $earnings[] = [
                'client_id'        => $sara->id,
                'earning_period_id'=> $saraP2->id,
                'date'             => $date,
                'btc_earned'       => 0.00196,
                'btc_price'        => 90000 + ($i * 100),
                'revenue'          => 0.00196 * (90000 + ($i * 100)),
                'additional_notes' => null,
            ];
        }

        // ── Sara – Period 3 (Mar 16–Apr 14, active — partial, 20 days) ───
        foreach (range(16, 35) as $i => $day) {
            $date = $day <= 31
                ? sprintf('2026-03-%02d', $day)
                : sprintf('2026-04-%02d', $day - 31);
            $earnings[] = [
                'client_id'        => $sara->id,
                'earning_period_id'=> $saraP3->id,
                'date'             => $date,
                'btc_earned'       => 0.00196,
                'btc_price'        => 93000 + ($i * 60),
                'revenue'          => 0.00196 * (93000 + ($i * 60)),
                'additional_notes' => null,
            ];
        }

        // Insert all earnings in one batch (bypasses observers intentionally)
        $now = now();
        $rows = array_map(fn($e) => array_merge($e, ['created_at' => $now, 'updated_at' => $now]), $earnings);

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('earnings')->insert($chunk);
        }

        // Recalculate period totals now that all earnings are inserted
        $allPeriods = [$ahmedP1, $ahmedP2, $ahmedP3, $mohammedP1, $mohammedP2, $saraP1, $saraP2, $saraP3];
        foreach ($allPeriods as $p) {
            $p->recalculateTotals();
        }

        $this->command->info('EarningSeeder: ' . count($rows) . ' earnings seeded and period totals recalculated.');
    }
}
