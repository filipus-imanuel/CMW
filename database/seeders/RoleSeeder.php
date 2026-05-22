<?php

namespace Database\Seeders;

use App\Helpers\CMW\PermissionHelper;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating roles and assigning permissions...');

        $roles = [
            'Super Admin',
            'Finance',
            'Sales',
            'Purchasing',
            'Warehouse',
            'Production',
            'Management',
            'Admin',
        ];

        foreach ($roles as $roleName) {
            // Create or get the role
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );

            // Get permissions for this role
            $permissions = PermissionHelper::getRolePermissions($roleName);

            // Sync permissions to role
            $role->syncPermissions($permissions);

            $this->command->info("✓ {$roleName}: ".count($permissions).' permissions assigned.');
        }

        $this->command->info('✓ All roles created and permissions assigned successfully.');
    }
}
