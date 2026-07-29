<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed admin account
        $this->call(AdminSeeder::class);

        // 2. Seed categories
        $this->call(CategorySeeder::class);

        // User & Book factories di-comment agar kosong (tanpa dummy)
        // User::factory(10)->create();
        // $this->command->info('✓ 10 sample users seeded.');

        // Book::factory(20)->create();
        // $this->command->info('✓ 20 sample books seeded.');
    }
}
