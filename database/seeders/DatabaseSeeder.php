<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed permissions and roles first
        $this->call([
            PermissionSeeder::class,
        ]);
        
        // Then seed users
        $this->call([
            UserSeeder::class,
        ]);
        
        // Then seed jenis unit
        $this->call([
            JenisUnitSeeder::class,
        ]);
        
        // Finally, assign roles to existing users
        $this->call([
            AssignRolesToUsers::class,
        ]);
    }
}
