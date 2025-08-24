<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role_name' => 'admin',
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Get all existing users without roles and assign them a default role (e.g., 'vendor')
        $defaultRole = Role::where('name', 'vendor')->first();
        
        if ($defaultRole) {
            $usersWithoutRoles = User::whereDoesntHave('roles')->get();
            foreach ($usersWithoutRoles as $user) {
                // If user has a role_name set in the database, use that
                if (!empty($user->role_name)) {
                    $role = Role::where('name', $user->role_name)->first();
                    if ($role) {
                        $user->assignRole($role);
                    } else {
                        $user->assignRole($defaultRole);
                    }
                } else {
                    $user->assignRole($defaultRole);
                }
            }
        }
    }
}
