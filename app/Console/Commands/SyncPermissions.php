<?php

namespace App\Console\Commands;

use App\Helpers\CMW\PermissionHelper;
use App\Models\User;
use Illuminate\Console\Command;

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
