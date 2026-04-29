<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\EarningPeriod;
use Illuminate\Database\Seeder;

class EarningPeriodSeeder extends Seeder
{
    public function run(): void
    {
        if (EarningPeriod::exists()) {
            $this->command->info('EarningPeriodSeeder: periods already exist, skipping.');
            return;
        }

        $ahmed    = Client::whereHas('user', fn($q) => $q->where('email', 'ahmed@client.com'))->firstOrFail();
        $mohammed = Client::whereHas('user', fn($q) => $q->where('email', 'mohammed@client.com'))->firstOrFail();
        $sara     = Client::whereHas('user', fn($q) => $q->where('email', 'sara@client.com'))->firstOrFail();

        /*
         * Periods follow the 30-day rule (start + 29 days = end).
         * Totals are left at 0 — EarningSeeder will recalculate them.
         *
         * Ahmed (5 machines):
         *   P1  Jan 01 – Jan 30  → cashed_out  (locked)
         *   P2  Jan 31 – Mar 01  → stored       (locked)
         *   P3  Mar 02 – Mar 31  → pending      (active, partial earnings)
         *
         * Mohammed (3 machines):
         *   P1  Feb 01 – Mar 02  → cashed_out  (locked)
         *   P2  Mar 03 – Apr 01  → completed   (awaiting client decision)
         *
         * Sara (7 machines):
         *   P1  Jan 15 – Feb 13  → stored       (locked)
         *   P2  Feb 14 – Mar 15  → request_pending (cashout, pending admin action)
         *   P3  Mar 16 – Apr 14  → pending      (active, partial earnings)
         */
        $periods = [
            // ── Ahmed ──────────────────────────────────────────────────────
            [
                'client_id'       => $ahmed->id,
                'start_date'      => '2026-01-01',
                'end_date'        => '2026-01-30',
                'status'          => EarningPeriod::STATUS_CASHED_OUT,
                'client_decision' => EarningPeriod::DECISION_CASHOUT,
                'is_locked'       => true,
                'requested_at'    => '2026-02-01 09:00:00',
                'processed_at'    => '2026-02-05 14:00:00',
            ],
            [
                'client_id'       => $ahmed->id,
                'start_date'      => '2026-01-31',
                'end_date'        => '2026-03-01',
                'status'          => EarningPeriod::STATUS_STORED,
                'client_decision' => EarningPeriod::DECISION_STORE,
                'is_locked'       => true,
                'requested_at'    => '2026-03-03 10:00:00',
                'processed_at'    => '2026-03-05 11:00:00',
            ],
            [
                'client_id'  => $ahmed->id,
                'start_date' => '2026-03-02',
                'end_date'   => '2026-03-31',
                'status'     => EarningPeriod::STATUS_PENDING,
            ],

            // ── Mohammed ────────────────────────────────────────────────────
            [
                'client_id'       => $mohammed->id,
                'start_date'      => '2026-02-01',
                'end_date'        => '2026-03-02',
                'status'          => EarningPeriod::STATUS_CASHED_OUT,
                'client_decision' => EarningPeriod::DECISION_CASHOUT,
                'is_locked'       => true,
                'requested_at'    => '2026-03-04 08:00:00',
                'processed_at'    => '2026-03-10 15:00:00',
            ],
            [
                'client_id'  => $mohammed->id,
                'start_date' => '2026-03-03',
                'end_date'   => '2026-04-01',
                'status'     => EarningPeriod::STATUS_COMPLETED,
            ],

            // ── Sara ────────────────────────────────────────────────────────
            [
                'client_id'       => $sara->id,
                'start_date'      => '2026-01-15',
                'end_date'        => '2026-02-13',
                'status'          => EarningPeriod::STATUS_STORED,
                'client_decision' => EarningPeriod::DECISION_STORE,
                'is_locked'       => true,
                'requested_at'    => '2026-02-15 09:00:00',
                'processed_at'    => '2026-02-20 12:00:00',
            ],
            [
                'client_id'       => $sara->id,
                'start_date'      => '2026-02-14',
                'end_date'        => '2026-03-15',
                'status'          => EarningPeriod::STATUS_REQUEST_PENDING,
                'client_decision' => EarningPeriod::DECISION_CASHOUT,
                'requested_at'    => '2026-03-16 10:00:00',
            ],
            [
                'client_id'  => $sara->id,
                'start_date' => '2026-03-16',
                'end_date'   => '2026-04-14',
                'status'     => EarningPeriod::STATUS_PENDING,
            ],
        ];

        $defaults = [
            'total_btc_earned'  => 0,
            'average_btc_price' => 0,
            'total_revenue'     => 0,
            'client_decision'   => null,
            'is_locked'         => false,
            'requested_at'      => null,
            'processed_at'      => null,
            'notes'             => null,
        ];

        foreach ($periods as $period) {
            EarningPeriod::create(array_merge($defaults, $period));
        }

        $this->command->info('EarningPeriodSeeder: 8 earning periods seeded.');
    }
}
