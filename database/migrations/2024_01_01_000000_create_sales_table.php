<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel 'sales' untuk menyimpan data penjualan.
     * Dataset: 411231179_Edisyah_Putra_Waruwu
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            // Primary Key — auto increment
            $table->id();

            // Tanggal transaksi penjualan (nullable untuk data yang tidak diketahui)
            $table->date('tanggal')->nullable();

            // Nama produk yang dijual
            $table->string('produk', 255)->nullable();

            // Kategori produk (Elektronik, ATK, Aksesoris, Edukasi, dll.)
            $table->string('kategori', 100)->nullable();

            // Jumlah unit yang terjual
            $table->unsignedInteger('jumlah');

            // Harga satuan produk (dalam Rupiah)
            $table->unsignedBigInteger('harga');

            // Total harga (jumlah * harga) — disimpan untuk performa query
            $table->unsignedBigInteger('total');

            // Timestamps: created_at & updated_at (opsional, bisa dihapus jika tidak perlu)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Menghapus tabel 'sales' saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
