<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Shelf;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin (PENTING: Ingat email/pass ini buat login nanti)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sd.com',
            'password' => bcrypt('password'), // Passwordnya "password"
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Buat Akun Pustakawan
        User::create([
            'name' => 'Petugas Budi',
            'email' => 'budi@sd.com',
            'password' => bcrypt('password'),
            'role' => 'pustakawan',
            'is_active' => true,
        ]);

        // 3. Buat Data Master (Kategori & Rak)
        $categories = Category::factory(5)->create(); // Buat 5 kategori
        $shelves    = Shelf::factory(5)->create();    // Buat 5 rak

        // 4. Buat 50 Buku
        // Kita loop agar setiap buku punya kategori & rak acak yang valid
        Book::factory(50)->make()->each(function ($book) use ($categories, $shelves) {
            $book->kategori_id = $categories->random()->id;
            $book->rak_id      = $shelves->random()->id;
            $book->save();
        });

        // 5. Buat 20 Anggota
        Member::factory(20)->create();

        // 6. Buat Setting Default
        \App\Models\Setting::create(['key' => 'denda_harian', 'value' => '500', 'type' => 'number']);
        \App\Models\Setting::create(['key' => 'max_buku', 'value' => '3', 'type' => 'number']);
    }
}
