<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (AdminPermission::all() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = User::where('email', 'superadmin@admin.com')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(AdminPermission::all());
            $superAdmin->update(['cashout_approval_level' => 3]);
        }

        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            $admin->syncPermissions(AdminPermission::all());
            $admin->update(['cashout_approval_level' => 1]);
        }
    }
}
