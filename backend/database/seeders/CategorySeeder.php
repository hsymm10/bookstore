<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Novel',
            'Komik',
            'Sains & Teknologi',
            'Sejarah',
            'Biografi',
            'Agama & Spiritual',
            'Pendidikan',
            'Anak-Anak',
            'Fiksi Ilmiah',
            'Self Development',
        ];

        foreach ($categories as $nama) {
            Category::updateOrCreate(
                ['slug' => Str::slug($nama)],
                [
                    'nama' => $nama,
                    'slug' => Str::slug($nama),
                ]
            );
        }

        $this->command->info('✓ ' . count($categories) . ' categories seeded.');
    }
}
