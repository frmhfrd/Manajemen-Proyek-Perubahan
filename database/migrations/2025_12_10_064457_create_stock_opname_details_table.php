<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books');
            $table->integer('stok_sistem'); // Stok menurut komputer saat itu
            $table->integer('stok_fisik');  // Stok nyata di rak
            $table->integer('selisih');     // Fisik - Sistem
            $table->string('keterangan')->nullable(); // Misal: "Hilang", "Rusak"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
