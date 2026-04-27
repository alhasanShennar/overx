<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@admin.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (! $superAdminUser->superAdmin()->exists()) {
            SuperAdmin::create(['user_id' => $superAdminUser->id]);
        }

        // Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (! $adminUser->admin()->exists()) {
            Admin::create(['user_id' => $adminUser->id]);
        }
    }
}
