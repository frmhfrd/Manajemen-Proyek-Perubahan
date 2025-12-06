<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_anggota' => fake()->unique()->numerify('2024####'), // NIS contoh: 20240001
            'nama_lengkap' => fake()->name(),
            'tipe_anggota' => fake()->randomElement(['siswa', 'guru']),
            'kelas'        => fake()->randomElement(['1A', '2B', '3A', '4C', '5B', '6A']),
            'wali_kelas'   => fake()->name(),
            'no_telepon'   => fake()->phoneNumber(),
            'alamat'       => fake()->address(),
            'status_aktif' => true,
        ];
    }
}
