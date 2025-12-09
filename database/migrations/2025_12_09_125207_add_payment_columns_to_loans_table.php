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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->after('status_transaksi');
            $table->string('midtrans_url')->nullable()->after('midtrans_order_id');
            $table->enum('status_pembayaran', ['unpaid', 'pending', 'paid'])->default('unpaid')->after('midtrans_url');
            $table->enum('tipe_pembayaran', ['tunai', 'online'])->default('tunai')->after('status_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['midtrans_order_id', 'midtrans_url', 'status_pembayaran', 'tipe_pembayaran']);
        });
    }
};
