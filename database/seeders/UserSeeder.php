<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@demo.com'],
            [
                'name' => 'Developer',
                'email' => 'demo@demo.com',
                'password' => Hash::make('secret'),

            ]
        );

        // Assign Super Admin role
        $user->assignRole('Super Admin');

        $this->command->info('✓ User created and assigned Super Admin role.');
    }
}
