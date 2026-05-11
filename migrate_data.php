<?php
use Illuminate\Support\Facades\DB;

try {
    echo "Memulai pemindahan data...\n";
    
    // Hapus data lama di sales jika ada
    DB::table('sales')->truncate();

    $query = "
        INSERT INTO sales (id, tanggal, produk, kategori, jumlah, harga, total)
        SELECT 
            id, 
            CASE 
                WHEN tanggal = 'Tidak Diketahui' OR tanggal = '' OR tanggal IS NULL THEN NULL 
                ELSE tanggal 
            END,
            CASE WHEN produk = 'Tidak Diketahui' THEN NULL ELSE produk END,
            CASE WHEN kategori = 'Tidak Diketahui' THEN NULL ELSE kategori END,
            jumlah,
            harga,
            total
        FROM tablename
        WHERE id IS NOT NULL AND id != '' AND id != 0
    ";

    DB::statement($query);
    
    $count = DB::table('sales')->count();
    echo "BERHASIL! $count baris data telah dipindahkan ke tabel 'sales'.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
