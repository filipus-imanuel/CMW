<?php

namespace App\Helpers\CMW;

use InvalidArgumentException;

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
                'company' => ['view', 'create', 'edit', 'delete'],
                'country' => ['view', 'create', 'edit', 'delete'],
                'credit term' => ['view', 'create', 'edit', 'delete'],
                'currency' => ['view', 'create', 'edit', 'delete'],
                'department' => ['view', 'create', 'edit', 'delete'],
                'employee' => ['view', 'create', 'edit', 'delete'],
                'exchange rate' => ['view', 'create', 'edit', 'delete'],
                'tax' => ['view', 'create', 'edit', 'delete'],
                'uom' => ['view', 'create', 'edit', 'delete'],
                'uom conversion' => ['view', 'create', 'edit', 'delete'],
                'user group' => ['view', 'create', 'edit', 'delete'],
                'warehouse' => ['view', 'create', 'edit', 'delete'],
            ],

            // ══════════════════════════════════════════════════════════════
            // PARTNERS
            // ══════════════════════════════════════════════════════════════
            'partners' => [
                'customer' => ['view', 'create', 'edit', 'delete'],
                'partner address' => ['view', 'create', 'edit', 'delete'],
                'supplier' => ['view', 'create', 'edit', 'delete'],
            ],

            // ══════════════════════════════════════════════════════════════
            // INVENTORY
            // ══════════════════════════════════════════════════════════════
            'inventory' => [
                'category price' => ['view', 'create', 'edit', 'delete'],
                'item' => ['view', 'create', 'edit', 'delete'],
                'item category' => ['view', 'create', 'edit', 'delete'],
                'item price' => ['view', 'create', 'edit', 'delete'],
                'item price history' => ['view'],
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
     * Get permissions for a specific role.
     *
     * @return array<string>
     *
     * @throws InvalidArgumentException
     */
    public static function getRolePermissions(string $role): array
    {
        $allPermissions = self::all();

        return match ($role) {
            'Super Admin' => $allPermissions,
            'Management' => array_values(array_filter($allPermissions, fn ($p) => str_contains($p, 'view'))),
            'Admin' => array_values(array_filter($allPermissions, function ($permission) {
                // Admin gets full CRUD on all master, partners, and inventory resources
                return str_contains($permission, 'country')
                    || str_contains($permission, 'department')
                    || str_contains($permission, 'employee')
                    || str_contains($permission, 'user group')
                    || str_contains($permission, 'uom')
                    || str_contains($permission, 'tax')
                    || str_contains($permission, 'credit term')
                    || str_contains($permission, 'warehouse')
                    || str_contains($permission, 'currency')
                    || str_contains($permission, 'exchange rate')
                    || str_contains($permission, 'company')
                    || str_contains($permission, 'supplier')
                    || str_contains($permission, 'customer')
                    || str_contains($permission, 'partner address')
                    || str_contains($permission, 'item')
                    || str_contains($permission, 'category price');
            })),
            'Finance' => array_values(array_filter($allPermissions, function ($permission) {
                // Finance gets full CRUD on financial master data
                return str_contains($permission, 'tax')
                    || str_contains($permission, 'currency')
                    || str_contains($permission, 'exchange rate')
                    || str_contains($permission, 'credit term')
                    || str_contains($permission, 'company');
            })),
            'Sales' => array_values(array_filter($allPermissions, function ($permission) {
                // Sales gets full CRUD on customers/addresses, view on financial master data and items
                $isSalesResource = str_contains($permission, 'customer') || str_contains($permission, 'partner address');
                $isViewOnly = str_contains($permission, 'view') && (
                    str_contains($permission, 'currency')
                    || str_contains($permission, 'tax')
                    || str_contains($permission, 'credit term')
                    || str_contains($permission, 'item')
                );

                return $isSalesResource || $isViewOnly;
            })),
            'Purchasing' => array_values(array_filter($allPermissions, function ($permission) {
                // Purchasing gets full CRUD on suppliers/addresses, view on financial master data and items
                $isPurchasingResource = str_contains($permission, 'supplier') || str_contains($permission, 'partner address');
                $isViewOnly = str_contains($permission, 'view') && (
                    str_contains($permission, 'currency')
                    || str_contains($permission, 'tax')
                    || str_contains($permission, 'credit term')
                    || str_contains($permission, 'item')
                );

                return $isPurchasingResource || $isViewOnly;
            })),
            'Warehouse' => array_values(array_filter($allPermissions, function ($permission) {
                // Warehouse gets full CRUD on warehouse, items, and category price
                return str_contains($permission, 'warehouse')
                    || str_contains($permission, 'item')
                    || str_contains($permission, 'category price');
            })),
            default => throw new InvalidArgumentException("Unknown role: {$role}"),
        };
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
