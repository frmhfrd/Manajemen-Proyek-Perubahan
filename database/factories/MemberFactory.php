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
        // 1. DAFTAR TEMAN ANDA
        $demoContacts = [
            ['name' => 'Rafael Pransa',         'hp' => '081313099878'],
            ['name' => 'Fahri Muhamad Firdaus', 'hp' => '085117047720'],
            ['name' => 'Aril Awali',            'hp' => '087898117703'],
            ['name' => 'Dafa Wafi Pradipa',     'hp' => '089506291487'],
            ['name' => 'Mochamad Dhafin Azriya','hp' => '082123703102'],
        ];

        // 2. Pilih 1 orang secara acak
        $friend = fake()->randomElement($demoContacts);

        return [
            // --- DATA OTOMATIS (Tetap Ada) ---
            'kode_anggota' => fake()->unique()->numerify('2024####'), // Contoh: 20249912
            'tipe_anggota' => fake()->randomElement(['siswa', 'guru']),
            'kelas'        => fake()->randomElement(['1A', '2B', '3A', '4C', '5B', '6A']),
            'alamat'       => fake()->address(),
            'status_aktif' => true,

            // --- DATA TEMAN (Biar Demo Keren) ---
            'nama_lengkap' => $friend['name'], // Nama Member jadi nama teman
            'no_telepon'   => $friend['hp'],   // HP Member jadi HP teman

            // Wali kelas kita isi nama acak saja (atau nama orang tua teman)
            'wali_kelas'   => fake()->name(),
        ];
    }
}
