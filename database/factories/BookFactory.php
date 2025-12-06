<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'kode_buku'    => fake()->unique()->isbn13(), // Barcode unik
            'judul'        => fake()->sentence(3), // Judul 3 kata
            'pengarang'    => fake()->name(),
            'penerbit'     => fake()->company(),
            'tahun_terbit' => fake()->year(),

            // Kita kosongkan dulu foreign key (nanti diisi otomatis oleh Seeder)
            'kategori_id'  => 1,
            'rak_id'       => 1,

            'stok_total'    => 10,
            'stok_tersedia' => 10, // Awalnya penuh
            'stok_rusak'    => 0,
            'stok_hilang'   => 0,
        ];
    }
}
