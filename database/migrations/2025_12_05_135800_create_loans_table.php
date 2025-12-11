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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 20)->unique();

            // Siapa pinjam, Siapa petugas
            $table->foreignId('member_id')->constrained('members');
            $table->foreignId('user_id')->constrained('users');

            $table->date('tgl_pinjam');
            $table->date('tgl_wajib_kembali');
            $table->date('tgl_kembali')->nullable();

            $table->string('tahun_ajaran', 10);
            $table->enum('status_transaksi', ['berjalan', 'selesai', 'terlambat'])->default('berjalan');
            $table->decimal('total_denda', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
