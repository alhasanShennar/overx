<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CurrencySeeder::class,
            ClientSeeder::class,
            EarningPeriodSeeder::class,
            EarningSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
