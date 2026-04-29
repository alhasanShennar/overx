<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'AED',
                'name' => 'United Arab Emirates Dirham',
                'symbol' => 'د.إ',
                'rate' => 1.000000,
                'is_active' => true,
                'is_default' => true,
                'order' => 1,
            ],
            [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'symbol' => 'ر.س',
                'rate' => 0.980000,
                'is_active' => true,
                'is_default' => false,
                'order' => 2,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate' => 3.673000,
                'is_active' => true,
                'is_default' => false,
                'order' => 3,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'rate' => 4.100000,
                'is_active' => true,
                'is_default' => false,
                'order' => 4,
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound Sterling',
                'symbol' => '£',
                'rate' => 4.700000,
                'is_active' => true,
                'is_default' => false,
                'order' => 5,
            ],
            [
                'code' => 'KWD',
                'name' => 'Kuwaiti Dinar',
                'symbol' => 'د.ك',
                'rate' => 0.300000,
                'is_active' => true,
                'is_default' => false,
                'order' => 6,
            ],
            [
                'code' => 'QAR',
                'name' => 'Qatari Riyal',
                'symbol' => 'ر.ق',
                'rate' => 1.010000,
                'is_active' => true,
                'is_default' => false,
                'order' => 7,
            ],
            [
                'code' => 'OMR',
                'name' => 'Omani Rial',
                'symbol' => 'ر.ع.',
                'rate' => 0.385000,
                'is_active' => true,
                'is_default' => false,
                'order' => 8,
            ],
            [
                'code' => 'BHD',
                'name' => 'Bahraini Dinar',
                'symbol' => 'د.ب',
                'rate' => 0.376000,
                'is_active' => true,
                'is_default' => false,
                'order' => 9,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
