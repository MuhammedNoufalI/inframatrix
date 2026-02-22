<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $infraAdminRole = Role::create(['name' => 'infra_admin']);
        $infraUserRole = Role::create(['name' => 'infra_user']);

        // 2. Create Default Admin User
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@timesworld.com',
            'password' => Hash::make('password'), 
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);

        // 3. Create Sample Infra Admin (optional for testing)
        // $infraAdmin = User::create([
        //     'name' => 'Infra Admin',
        //     'email' => 'infra@timesworld.com',
        //     'password' => Hash::make('password'),
        // ]);
        // $infraAdmin->assignRole($infraAdminRole);
    }
}
