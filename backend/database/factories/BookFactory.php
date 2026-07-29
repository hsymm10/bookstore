<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hargaModal = fake()->numberBetween(20000, 150000);
        $hargaJual  = $hargaModal + fake()->numberBetween(5000, 50000);
        $namaBuku   = fake()->unique()->sentence(3);

        return [
            'category_id'    => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'nama_buku'      => $namaBuku,
            'slug'           => Str::slug($namaBuku),
            'tanggal_terbit' => fake()->date(),
            'tahun_terbit'   => fake()->year(),
            'stok'           => fake()->numberBetween(5, 100),
            'harga_modal'    => $hargaModal,
            'harga_jual'     => $hargaJual,
            'keuntungan'     => $hargaJual - $hargaModal,
            'deskripsi'      => fake()->paragraphs(3, true),
            'gambar'         => null,
        ];
    }
}
