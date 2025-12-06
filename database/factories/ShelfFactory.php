<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shelf>
 */
class ShelfFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_rak' => 'Rak ' . fake()->bothify('?-##'), // Hasil: Rak A-01, Rak B-99
            'lokasi'   => fake()->randomElement(['Pojok Baca Kls 1', 'Perpus Pusat', 'Lemari Kaca']),
        ];
    }
}
