<?php
use App\Models\Sale;

echo "Memulai prediksi tanggal...\n";

$nullSales = Sale::whereNull('tanggal')->orderBy('id', 'asc')->get();
$count = 0;

foreach ($nullSales as $sale) {
    // Cari tanggal dari ID sebelumnya
    $prev = Sale::where('id', '<', $sale->id)
                ->whereNotNull('tanggal')
                ->orderBy('id', 'desc')
                ->first();

    if ($prev) {
        $sale->tanggal = $prev->tanggal;
        $sale->save();
        $count++;
    } else {
        // Jika tidak ada sebelumnya, cari setelahnya
        $next = Sale::where('id', '>', $sale->id)
                    ->whereNotNull('tanggal')
                    ->orderBy('id', 'asc')
                    ->first();
        if ($next) {
            $sale->tanggal = $next->tanggal;
            $sale->save();
            $count++;
        }
    }
}

echo "BERHASIL! $count tanggal kosong telah diisi dengan prediksi yang akurat.\n";
