<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->unique()->randomElement([
            'Novel', 'Komik', 'Sains', 'Sejarah', 'Biografi',
            'Teknologi', 'Agama', 'Anak-Anak', 'Pendidikan', 'Fiksi Ilmiah',
        ]);

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama),
        ];
    }
}
