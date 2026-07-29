<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'role'     => 'admin',
                'nama'     => 'Administrator',
                'username' => 'admin',
                'email'    => 'admin@bookstore.com',
                'no_hp'    => '081234567890',
                'password' => Hash::make('admin123'),
            ]
        );

        $this->command->info('✓ Admin seeded: username=admin, password=admin123');
    }
}
