<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Harvest Planning
            'view-gis-information',
            'upload-gis',
            'create-gis-information',
            'edit-gis-information',
            'delete-gis-information',
            'view-sub-block-information',
            'create-sub-block-information',
            'edit-sub-block-information',
            'delete-sub-block-information',
            'create-status-sub-block',
            'edit-status-sub-block',
            'delete-status-sub-block',
            'view-status-sub-block',
            'view-harvest-sub-block',
            'create-harvest-sub-block',
            'edit-harvest-sub-block',
            'delete-harvest-sub-block',
            'view-foreman-sub-block',
            'create-foreman-sub-block',
            'edit-foreman-sub-block',
            'delete-foreman-sub-block',

            // Vendor Management
            'view-vendors',
            'create-vendor',
            'edit-vendor',
            'delete-vendor',
            'view-vehicles',
            'create-vehicle',
            'edit-vehicle',
            'delete-vehicle',

            // Mandor Management
            'view-mandors',
            'create-mandor',
            'edit-mandor',
            'delete-mandor',

            // Harvest Activity
            'create-spt',
            'view-spt',
            'edit-spt',
            'delete-spt',
            'approve-spt',
            'view-approval-progress',
            'view-lkt',
            'create-lkt',
            'edit-lkt',
            'approve-lkt',
            'view-track-activity',
            'edit-track-activity',
            'view-hasil-tebang',
            'edit-hasil-tebang',
            'delete-hasil-tebang',
            'generate-bapp',
            'view-bapp',

            // SPD Permissions
            'view-spd',
            'create-spd',
            'edit-spd',
            'delete-spd',
            'approve-spd',
            'reject-spd',
            'view-spd-approval',

            // Payment Management
            'view-bapp',
            'create-bapp',
            'edit-bapp',
            'delete-bapp',
            'approve-bapp',
            'view-bapp-recap',
            'approve-bapp-recap',   
            'view-payment-calculation',
            'calculate-payment',
            'approve-payment',

            // To-Do Approval
            'view-dana',
            'view-approval-spt',
            'view-approval-lkt',
            'view-approval-bapp',
            'approve-dana',

            // System & Access Control
            'view-users',
            'create-user',
            'edit-user',
            'delete-user',
            'register-user',
            'view-roles',
            'create-role',
            'edit-role',
            'delete-role',
            'view-permissions',
            'manage-permissions',
            'download-layak-tebang',
        ];

        // Create each permission if not exists
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // === Roles ===

        // Define all roles with their display names and descriptions
        $roles = [
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Has full access to all features',
                'permissions' => 'all' // Will get all permissions
            ],
            'GIS Division' => [
                'display_name' => 'GIS Division',
                'description' => 'GIS Division role with access to manage maps',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'upload-gis',
                    'create-gis-information',
                    'edit-gis-information',
                    'delete-gis-information',
                    'download-layak-tebang',
                    'create-sub-block-information',
                    'edit-sub-block-information',
                    'delete-sub-block-information',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'create-status-sub-block',
                    'edit-status-sub-block',
                    'delete-status-sub-block',
                    'view-harvest-sub-block',
                    'create-harvest-sub-block',
                    'edit-harvest-sub-block',
                    'delete-harvest-sub-block',
                    'view-foreman-sub-block',
                ]
            ],
            'vendor' => [
                'display_name' => 'Vendor',
                'description' => 'Vendor role with limited access',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'view-harvest-sub-block',
                    'view-sub-block-information',
                    'view-spt',
                    'approve-spt',
                    'view-lkt',
                    'view-bapp',
                    'approve-bapp',
                    'view-approval-spt',
                    'view-approval-bapp',
                    'view-track-activity',
                ]
            ],
            'mandor' => [
                'display_name' => 'Mandor',
                'description' => 'Mandor role with field operation access',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-foreman-sub-block',
                    'view-harvest-sub-block',
                    'view-vendors',
                    'view-vehicles',
                    'view-spt',
                    'create-lkt',
                    'view-lkt',
                    'edit-lkt',
                    'view-track-activity',
                    'edit-track-activity',
                ]
            ],
            'finance' => [
                'display_name' => 'Finance',
                'description' => 'Finance department role',
                'permissions' => [
                    'view-dashboard',
                    'view-bapp',
                    'approve-bapp',
                    'view-payment-calculation',
                    'calculate-payment',
                    'approve-payment',
                    'view-approval-bapp',
                ]
            ],
            'Assistant Divisi Plantation' => [
                'display_name' => 'Assistant Divisi Plantation',
                'description' => 'Assistant Divisi Plantation role',
                'permissions' => [
                    'view-dashboard',
                    'view-vendors',
                    'create-vendor',
                    'edit-vendor',
                    'delete-vendor',
                    'view-mandors',
                    'create-mandor',
                    'edit-mandor',
                    'delete-mandor',        
                    'view-vehicles',
                    'create-vehicle',
                    'edit-vehicle',
                    'delete-vehicle',
                    'view-gis-information',
                    'create-spt',
                    'view-spt',
                    'edit-spt',
                    'approve-spt',
                    'view-approval-progress',
                    'view-lkt',
                    'edit-lkt',
                    'approve-lkt',
                    'view-track-activity',
                    'view-approval-lkt',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'create-foreman-sub-block',
                    'edit-foreman-sub-block',
                    'delete-foreman-sub-block',
                ]
            ],
            'Assistant Manager Plantation' => [
                'display_name' => 'Assistant Manager Plantation',
                'description' => 'Assistant Manager Plantation role',
                'permissions' => [
                    'view-dashboard',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'view-mandors',
                    'view-vendors',
                    'view-vehicles',
                    'view-gis-information',
                    'view-spt',
                    'approve-spt',
                    'view-approval-progress',
                    'view-lkt',
                    'approve-lkt',
                    'view-track-activity',
                    'view-approval-lkt',
                    'view-bapp',
                    'approve-bapp',
                    'view-approval-bapp',
                    'view-hasil-tebang',
                ]
            ],
            'Manager Plantation' => [
                'display_name' => 'Manager Plantation',
                'description' => 'Manager Plantation role',
                'permissions' => [
                    'view-dashboard',
                    'view-mandors',
                    'view-vendors',
                    'view-vehicles',
                    'view-gis-information',
                    'view-spt',
                    'approve-spt',
                    'view-approval-progress',
                    'view-lkt',
                    'approve-lkt',
                    'view-track-activity',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'view-bapp',
                    'approve-bapp',
                    'view-approval-bapp',
                    'view-bapp',
                    'view-bapp-recap',
                    'approve-bapp-recap',
                    'view-dana',
                    'view-approval-bapp',
                    'view-payment-calculation',
                    'approve-dana',
                    'view-hasil-tebang',
                ]
            ],
            'Assistant Manager CDR' => [
                'display_name' => 'Assistant Manager CDR',
                'description' => 'Assistant Manager CDR role',
                'permissions' => [
                    'view-dashboard',
                    'view-bapp',
                    'edit-bapp',
                    'delete-bapp',
                    'approve-bapp',
                    'view-approval-bapp',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'view-mandors',
                    'view-vendors',
                    'view-vehicles',
                    'view-spt',
                    'view-lkt',
                    'view-track-activity',
                    'view-bapp',
                    'view-bapp-recap',
                    'view-dana',
                    'view-approval-bapp',
                    'view-payment-calculation',
                    'generate-bapp',
                    'view-hasil-tebang',

                ]
            ],
            'Manager CDR' => [
                'display_name' => 'Manager CDR',
                'description' => 'Manager CDR role',
                'permissions' => [
                    'view-dashboard',
                    'view-bapp',
                    'approve-bapp',
                    'view-approval-bapp',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'view-mandors',
                    'view-vendors',
                    'view-vehicles',
                    'view-spt',
                    'view-lkt',
                    'view-track-activity',
                    'view-bapp',
                    'view-bapp-recap',
                    'view-dana',
                    'approve-dana',
                    'view-approval-bapp',
                    'view-payment-calculation',
                    'view-hasil-tebang',
                ]
            ],
            'Manager Finance' => [
                'display_name' => 'Manager Finance',
                'description' => 'Manager Finance role with approval access',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-harvest-sub-block',
                    'view-lkt',
                    'view-track-activity',
                    'view-dana',
                    'approve-dana',
                    'view-bapp',
                    'view-bapp-recap',
                    'approve-bapp-recap',
                    'view-approval-bapp',
                    'view-payment-calculation',
                    'approve-payment',

                    
                ]
            ],
            'Assistant Finance' => [
                'display_name' => 'Assistant Finance',
                'description' => 'Assistant Finance role with approval access',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-harvest-sub-block',
                    'view-bapp',
                    'view-bapp-recap',
                    'approve-bapp-recap',
                    'view-dana',
                    'view-approval-bapp',
                    'view-payment-calculation',
                ]
            ],
            'QA' => [
                'display_name' => 'QA',
                'description' => 'QA role with approval access',
                'permissions' => [
                    'view-dashboard',
                    'view-gis-information',
                    'view-sub-block-information',
                    'view-harvest-sub-block',
                    'view-bapp',
                    'approve-bapp',
                    'approve-bapp-recap',
                    'view-bapp-recap',
                    'view-dana',
                    'approve-dana',

                ]
            ],

            'PT PAG' => [
                'display_name' => 'PT PAG',
                'description' => 'PT PAG role with approval access',
                'permissions' => [
                    'view-dashboard',
                    'view-lkt',
                    'approve-lkt',
                    'view-hasil-tebang',
                    'edit-hasil-tebang',
                    'delete-hasil-tebang',
                ]
            ],


            'Director' => [
                'display_name' => 'Director',
                'description' => 'Director role with approval access',
                'permissions' => [
                    'view-dashboard',
                    'view-mandors',
                    'view-vendors',
                    'view-vehicles',
                    'view-gis-information',
                    'view-spt',
                    'approve-spt',
                    'view-approval-progress',
                    'view-lkt',
                    'view-track-activity',
                    'view-sub-block-information',
                    'view-status-sub-block',
                    'view-harvest-sub-block',
                    'view-foreman-sub-block',
                    'view-bapp',
                    'approve-bapp',
                    'view-approval-bapp',
                    'approve-payment',
                    'view-track-activity',
                    'view-bapp-recap',
                    'approve-bapp-recap',
                    'view-dana',
                    'approve-dana',
                    'view-payment-calculation',
                    'approve-payment',

                ]
            ],
        ];

        // Create or update roles and assign permissions
        foreach ($roles as $roleName => $roleData) {
            // First create the role with only the required fields
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );

            // Store display_name and description in a separate table or as metadata if needed
            // For now, we'll just log them
            $this->command->info("Creating role: {$roleName} - {$roleData['display_name']} - {$roleData['description']}");

            // Assign permissions
            if ($roleData['permissions'] === 'all') {
                $allPermissions = Permission::all()->pluck('name')->toArray();
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions($roleData['permissions']);
            }
        }


    }
}
