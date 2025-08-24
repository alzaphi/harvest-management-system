<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRolesToUsers extends Seeder
{
    public function run()
    {
        // Get all users with a role_name
        $users = User::whereNotNull('role_name')->get();
        
        foreach ($users as $user) {
            // Find the role by name
            $role = Role::where('name', $user->role_name)->first();
            
            if ($role) {
                // Remove any existing roles
                $user->roles()->detach();
                // Assign the new role
                $user->assignRole($role);
                $this->command->info("Assigned role '{$role->name}' to user '{$user->name}'");
            } else {
                $this->command->warn("Role '{$user->role_name}' not found for user '{$user->name}'");
            }
        }
        
        // If no users with role_name, assign default 'vendor' role to all users
        if ($users->isEmpty()) {
            $defaultRole = Role::where('name', 'vendor')->first();
            if ($defaultRole) {
                $users = User::doesntHave('roles')->get();
                foreach ($users as $user) {
                    $user->assignRole($defaultRole);
                    $this->command->info("Assigned default role 'vendor' to user '{$user->name}'");
                }
            }
        }
    }
}
