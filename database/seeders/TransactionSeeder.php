<?php

namespace Database\Seeders;

use App\Models\Cashout;
use App\Models\CashoutDetail;
use App\Models\Client;
use App\Models\Currency;
use App\Models\EarningPeriod;
use App\Models\StoredEarning;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        if (Transaction::exists()) {
            $this->command->info('TransactionSeeder: transactions already exist, skipping.');
            return;
        }

        $usdId = Currency::where('code', 'USD')->value('id');
        $kwdId = Currency::where('code', 'KWD')->value('id');

        $ahmed    = Client::whereHas('user', fn($q) => $q->where('email', 'ahmed@client.com'))->firstOrFail();
        $mohammed = Client::whereHas('user', fn($q) => $q->where('email', 'mohammed@client.com'))->firstOrFail();
        $sara     = Client::whereHas('user', fn($q) => $q->where('email', 'sara@client.com'))->firstOrFail();

        $period = fn(int $clientId, string $start) =>
            EarningPeriod::where('client_id', $clientId)->where('start_date', $start)->firstOrFail();

        // ── Ahmed P1 → cashout (completed) ─────────────────────────────────
        $ahmedP1 = $period($ahmed->id, '2026-01-01');
        $ahmedP1Detail = CashoutDetail::where('client_id', $ahmed->id)->where('is_default', true)->first();
        $t1 = Transaction::create([
            'client_id'        => $ahmed->id,
            'earning_period_id'=> $ahmedP1->id,
            'currency_id'      => $usdId,
            'type'             => Transaction::TYPE_CASHOUT,
            'btc_amount'       => $ahmedP1->total_btc_earned,
            'fiat_amount'      => $ahmedP1->total_revenue,
            'status'           => Transaction::STATUS_COMPLETED,
            'requested_by'     => 'client',
            'requested_at'     => '2026-02-01 09:00:00',
            'processed_at'     => '2026-02-05 14:00:00',
            'notes'            => null,
        ]);
        Cashout::create([
            'client_id'         => $ahmed->id,
            'transaction_id'    => $t1->id,
            'cashout_details_id'=> $ahmedP1Detail?->id,
            'amount'            => $ahmedP1->total_revenue,
            'btc_amount'        => $ahmedP1->total_btc_earned,
            'date'              => '2026-02-05',
            'status'            => 'completed',
            'notes'             => null,
        ]);

        // ── Ahmed P2 → store (completed) ────────────────────────────────────
        $ahmedP2 = $period($ahmed->id, '2026-01-31');
        $t2 = Transaction::create([
            'client_id'        => $ahmed->id,
            'earning_period_id'=> $ahmedP2->id,
            'currency_id'      => null,
            'type'             => Transaction::TYPE_STORE,
            'btc_amount'       => $ahmedP2->total_btc_earned,
            'fiat_amount'      => $ahmedP2->total_revenue,
            'status'           => Transaction::STATUS_COMPLETED,
            'requested_by'     => 'client',
            'requested_at'     => '2026-03-03 10:00:00',
            'processed_at'     => '2026-03-05 11:00:00',
            'notes'            => null,
        ]);
        StoredEarning::create([
            'client_id'        => $ahmed->id,
            'transaction_id'   => $t2->id,
            'earning_period_id'=> $ahmedP2->id,
            'btc_amount'       => $ahmedP2->total_btc_earned,
            'revenue_amount'   => $ahmedP2->total_revenue,
            'stored_at'        => '2026-03-05 11:00:00',
            'notes'            => null,
        ]);

        // ── Mohammed P1 → cashout (completed) ──────────────────────────────
        $mohammedP1 = $period($mohammed->id, '2026-02-01');
        $mohammedDetail = CashoutDetail::where('client_id', $mohammed->id)->where('is_default', true)->first();
        $t3 = Transaction::create([
            'client_id'        => $mohammed->id,
            'earning_period_id'=> $mohammedP1->id,
            'currency_id'      => null,
            'type'             => Transaction::TYPE_CASHOUT,
            'btc_amount'       => $mohammedP1->total_btc_earned,
            'fiat_amount'      => $mohammedP1->total_revenue,
            'status'           => Transaction::STATUS_COMPLETED,
            'requested_by'     => 'client',
            'requested_at'     => '2026-03-04 08:00:00',
            'processed_at'     => '2026-03-10 15:00:00',
            'notes'            => null,
        ]);
        Cashout::create([
            'client_id'         => $mohammed->id,
            'transaction_id'    => $t3->id,
            'cashout_details_id'=> $mohammedDetail?->id,
            'amount'            => $mohammedP1->total_revenue,
            'btc_amount'        => $mohammedP1->total_btc_earned,
            'date'              => '2026-03-10',
            'status'            => 'completed',
            'notes'             => null,
        ]);

        // ── Sara P1 → store (completed) ─────────────────────────────────────
        $saraP1 = $period($sara->id, '2026-01-15');
        $t4 = Transaction::create([
            'client_id'        => $sara->id,
            'earning_period_id'=> $saraP1->id,
            'currency_id'      => null,
            'type'             => Transaction::TYPE_STORE,
            'btc_amount'       => $saraP1->total_btc_earned,
            'fiat_amount'      => $saraP1->total_revenue,
            'status'           => Transaction::STATUS_COMPLETED,
            'requested_by'     => 'client',
            'requested_at'     => '2026-02-15 09:00:00',
            'processed_at'     => '2026-02-20 12:00:00',
            'notes'            => null,
        ]);
        StoredEarning::create([
            'client_id'        => $sara->id,
            'transaction_id'   => $t4->id,
            'earning_period_id'=> $saraP1->id,
            'btc_amount'       => $saraP1->total_btc_earned,
            'revenue_amount'   => $saraP1->total_revenue,
            'stored_at'        => '2026-02-20 12:00:00',
            'notes'            => null,
        ]);

        // ── Sara P2 → cashout (request_pending — awaiting admin) ────────────
        $saraP2 = $period($sara->id, '2026-02-14');
        $saraDefaultDetail = CashoutDetail::where('client_id', $sara->id)->where('is_default', true)->first();
        Transaction::create([
            'client_id'        => $sara->id,
            'earning_period_id'=> $saraP2->id,
            'currency_id'      => $usdId,
            'type'             => Transaction::TYPE_CASHOUT,
            'btc_amount'       => $saraP2->total_btc_earned,
            'fiat_amount'      => $saraP2->total_revenue,
            'status'           => Transaction::STATUS_PENDING,
            'requested_by'     => 'client',
            'requested_at'     => '2026-03-16 10:00:00',
            'processed_at'     => null,
            'notes'            => null,
        ]);

        $this->command->info('TransactionSeeder: transactions, cashouts and stored_earnings seeded.');
    }
}
