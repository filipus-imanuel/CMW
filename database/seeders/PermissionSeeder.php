<?php

namespace Database\Seeders;

use App\Helpers\CMW\PermissionHelper;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Syncing permissions from PermissionHelper...');

        // Sync all permissions to database
        PermissionHelper::sync();

        $totalPermissions = count(PermissionHelper::all());
        $this->command->info("✓ {$totalPermissions} permissions synced successfully.");
    }
}
