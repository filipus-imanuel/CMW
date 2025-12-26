<?php

namespace App\Helpers\CMW;

class PermissionHelper
{
    /**
     * Get the master permission matrix.
     *
     * @return array<string, array<string, array<string>>>
     */
    public static function master(): array
    {
        return [
            // ══════════════════════════════════════════════════════════════
            // MASTER DATA
            // ══════════════════════════════════════════════════════════════
            'master' => [
                'country' => ['view', 'create', 'edit', 'delete'],
                'position' => ['view', 'create', 'edit', 'delete'],
                'employee' => ['view', 'create', 'edit', 'delete'],
                'user group' => ['view', 'create', 'edit', 'delete'],
                'uom' => ['view', 'create', 'edit', 'delete'],
                'uom conversion' => ['view', 'create', 'edit', 'delete'],
                'tax' => ['view', 'create', 'edit', 'delete'],
                'credit term' => ['view', 'create', 'edit', 'delete'],
                'warehouse' => ['view', 'create', 'edit', 'delete'],
            ],

            // ══════════════════════════════════════════════════════════════
            // PARTNERS
            // ══════════════════════════════════════════════════════════════
            'partners' => [
                'supplier' => ['view', 'create', 'edit', 'delete'],
                'customer' => ['view', 'create', 'edit', 'delete'],
                'partner address' => ['view', 'create', 'edit', 'delete'],
            ],

            // ══════════════════════════════════════════════════════════════
            // INVENTORY
            // ══════════════════════════════════════════════════════════════
            'inventory' => [
                'item' => ['view', 'create', 'edit', 'delete'],
            ],

            // ══════════════════════════════════════════════════════════════
            // EXTRA PERMISSIONS (non-standard actions)
            // ══════════════════════════════════════════════════════════════
            'extra' => [
                // 'resource' => ['custom action'],
            ],
        ];
    }

    /**
     * Get all permissions as a flat array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::master() as $group => $resources) {
            if ($group === 'extra') {
                foreach ($resources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $permissions[] = "{$action} {$resource}";
                    }
                }
            } else {
                foreach ($resources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $permissions[] = "{$action} {$resource}";
                    }
                }
            }
        }

        return array_unique($permissions);
    }

    /**
     * Get permissions grouped by resource.
     *
     * @return array<string, array<string>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::master() as $group => $resources) {
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    $grouped[$resource][] = "{$action} {$resource}";
                }
            }
        }

        return $grouped;
    }

    /**
     * Sync permissions to database.
     * Call via: php artisan permission:sync
     *
     * @param  array<string>|null  $roles
     */
    public static function sync(?array $roles = null): void
    {
        $permissionClass = config('permission.models.permission');
        $roleClass = config('permission.models.role');

        // Create all permissions
        foreach (self::all() as $permission) {
            $permissionClass::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Optionally assign to roles
        if ($roles) {
            foreach ($roles as $roleName) {
                $role = $roleClass::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->syncPermissions(self::all());
            }
        }
    }
}
