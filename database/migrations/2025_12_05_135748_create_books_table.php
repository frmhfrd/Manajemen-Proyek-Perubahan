<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('kode_buku', 50)->unique();
            $table->string('judul');
            $table->string('pengarang', 100);
            $table->string('penerbit', 100)->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->foreignId('kategori_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('rak_id')->constrained('shelves')->onDelete('cascade');
            $table->integer('stok_total')->default(0);
            $table->integer('stok_tersedia')->default(0);
            $table->integer('stok_rusak')->default(0);
            $table->integer('stok_hilang')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
