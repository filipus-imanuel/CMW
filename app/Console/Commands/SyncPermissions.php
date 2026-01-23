<?php

namespace App\Console\Commands;

use App\Helpers\CMW\PermissionHelper;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync {--user= : User ID to assign all permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from PermissionHelper to database and optionally assign to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing permissions...');

        // Sync permissions to database
        PermissionHelper::sync();

        $this->info('✓ Permissions synced successfully.');
        $this->info('Total permissions: '.count(PermissionHelper::all()));

        // Sync roles and their permissions
        $this->info("\nSyncing roles and permissions...");

        $roles = [
            'Super Admin',
            'Finance',
            'Sales',
            'Purchasing',
            'Warehouse',
            'Management',
            'Admin',
        ];

        $rolePermissionCounts = [];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );

            $permissions = PermissionHelper::getRolePermissions($roleName);
            $role->syncPermissions($permissions);

            $rolePermissionCounts[] = [
                'Role' => $roleName,
                'Permissions' => count($permissions),
            ];
        }

        $this->table(['Role', 'Permissions'], $rolePermissionCounts);
        $this->info('✓ All roles synced successfully.');

        // Assign to user if specified
        if ($userId = $this->option('user')) {
            $user = User::find($userId);

            if (! $user) {
                $this->error("User with ID {$userId} not found.");

                return self::FAILURE;
            }

            $user->givePermissionTo(PermissionHelper::all());

            $this->info("✓ All permissions assigned to user: {$user->name} ({$user->email})");
        }

        return self::SUCCESS;
    }
}
