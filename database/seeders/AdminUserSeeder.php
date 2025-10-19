<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Create Super Admin role if it doesn't exist
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

        // Assign super_admin role to the admin user
        $admin->assignRole($superAdminRole);
    }
}
