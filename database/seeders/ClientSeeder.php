<?php

namespace Database\Seeders;

use App\Models\CashoutDetail;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        if (Client::exists()) {
            $this->command->info('ClientSeeder: clients already exist, skipping.');
            return;
        }

        $usdId  = Currency::where('code', 'USD')->value('id');
        $kwdId  = Currency::where('code', 'KWD')->value('id');

        $clients = [
            // ─── Ahmed Al-Mansouri ─────────────────────────────────────────
            [
                'user'   => ['name' => 'Ahmed Al-Mansouri', 'email' => 'ahmed@client.com'],
                'client' => ['phone' => '+971501234567', 'current_storing_machines' => 3, 'current_cashout_machines' => 2],
                'contracts' => [
                    [
                        'amount'              => 50000.00,
                        'storing_machines_no' => 3,
                        'cashout_machines_no' => 2,
                        'start_date'          => '2026-01-01',
                        'end_date'            => '2026-12-31',
                        'notes'               => 'Initial mining contract',
                    ],
                ],
                'cashout_details' => [
                    [
                        'label'                => 'USDT TRC20',
                        'type'                 => 'crypto',
                        'crypto_wallet_address'=> 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE',
                        'crypto_network'       => 'TRC20',
                        'is_default'           => true,
                        'currency_id'          => $usdId,
                    ],
                    [
                        'label'          => 'Emirates NBD',
                        'type'           => 'bank',
                        'account_holder' => 'Ahmed Al-Mansouri',
                        'bank_name'      => 'Emirates NBD',
                        'iban'           => 'AE070331234567890123456',
                        'swift_code'     => 'EBILAEAD',
                        'is_default'     => false,
                        'currency_id'    => $usdId,
                    ],
                ],
            ],

            // ─── Mohammed Al-Rashid ────────────────────────────────────────
            [
                'user'   => ['name' => 'Mohammed Al-Rashid', 'email' => 'mohammed@client.com'],
                'client' => ['phone' => '+966501234567', 'current_storing_machines' => 2, 'current_cashout_machines' => 1],
                'contracts' => [
                    [
                        'amount'              => 30000.00,
                        'storing_machines_no' => 2,
                        'cashout_machines_no' => 1,
                        'start_date'          => '2026-02-01',
                        'end_date'            => '2026-12-31',
                        'notes'               => null,
                    ],
                ],
                'cashout_details' => [
                    [
                        'label'                => 'Bitcoin Wallet',
                        'type'                 => 'crypto',
                        'crypto_wallet_address'=> '1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2',
                        'crypto_network'       => 'BTC',
                        'is_default'           => true,
                        'currency_id'          => null,
                    ],
                ],
            ],

            // ─── Sara Al-Khalidi ───────────────────────────────────────────
            [
                'user'   => ['name' => 'Sara Al-Khalidi', 'email' => 'sara@client.com'],
                'client' => ['phone' => '+96551234567', 'current_storing_machines' => 4, 'current_cashout_machines' => 3],
                'contracts' => [
                    [
                        'amount'              => 75000.00,
                        'storing_machines_no' => 4,
                        'cashout_machines_no' => 3,
                        'start_date'          => '2026-01-15',
                        'end_date'            => '2026-12-31',
                        'notes'               => 'Premium mining plan',
                    ],
                ],
                'cashout_details' => [
                    [
                        'label'                => 'USDT ERC20',
                        'type'                 => 'crypto',
                        'crypto_wallet_address'=> '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
                        'crypto_network'       => 'ERC20',
                        'is_default'           => true,
                        'currency_id'          => $usdId,
                    ],
                    [
                        'label'          => 'Kuwait Finance House',
                        'type'           => 'bank',
                        'account_holder' => 'Sara Al-Khalidi',
                        'bank_name'      => 'Kuwait Finance House',
                        'iban'           => 'KW81CBKU0000000000001234560101',
                        'swift_code'     => 'KFHOKWKW',
                        'is_default'     => false,
                        'currency_id'    => $kwdId,
                    ],
                ],
            ],
        ];

        foreach ($clients as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], ['password' => Hash::make('password')])
            );

            $client = Client::firstOrCreate(
                ['user_id' => $user->id],
                $data['client']
            );

            foreach ($data['contracts'] as $contractData) {
                Contract::firstOrCreate(
                    ['client_id' => $client->id, 'start_date' => $contractData['start_date']],
                    array_merge($contractData, ['client_id' => $client->id])
                );
            }

            foreach ($data['cashout_details'] as $detailData) {
                CashoutDetail::firstOrCreate(
                    ['client_id' => $client->id, 'label' => $detailData['label']],
                    array_merge($detailData, ['client_id' => $client->id])
                );
            }
        }

        $this->command->info('ClientSeeder: 3 clients seeded.');
    }
}
