<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends Controller
{
    /**
     * Dashboard Utama: Menampilkan 4 Analisis Utama & 4 Grafik
     */
    public function dashboard()
    {
        // 1. Analisis Tren Penjualan Keseluruhan (Line Chart)
        $trenPenjualan = Sale::selectRaw('DATE(tanggal) as tgl')
            ->selectRaw('SUM(total) as total_revenue')
            ->selectRaw('COUNT(id) as total_transaksi')
            ->whereNotNull('tanggal')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        // 2. Analisis Total Penjualan Per Produk (Bar Chart)
        $penjualanPerProduk = Sale::select('produk')
            ->selectRaw('SUM(total) as total_revenue')
            ->whereNotNull('produk')
            ->groupBy('produk')
            ->orderByDesc('total_revenue')
            ->get();

        // 3. Analisis Penjualan Per Kategori Per Bulan (Grouped Bar Chart)
        $rawKategoriBulanan = Sale::select('kategori')
            ->selectRaw("DATE_FORMAT(tanggal, '%M') as bulan")
            ->selectRaw('MONTH(tanggal) as bulan_num')
            ->selectRaw('SUM(total) as revenue')
            ->whereNotNull('tanggal')
            ->groupBy('kategori', 'bulan', 'bulan_num')
            ->orderBy('bulan_num')
            ->get();

        // Aligment data untuk chart (memastikan urutan bulan konsisten di semua dataset)
        $labelsBulan = $rawKategoriBulanan->pluck('bulan')->unique()->values();
        $penjualanKategoriBulanan = [];

        foreach ($rawKategoriBulanan->groupBy('kategori') as $kategori => $items) {
            $data = [];
            foreach ($labelsBulan as $bulan) {
                $found = $items->firstWhere('bulan', $bulan);
                $data[] = $found ? $found->revenue : 0;
            }
            $penjualanKategoriBulanan[] = [
                'kategori' => $kategori,
                'data' => $data
            ];
        }

        // 4. Analisis Proporsi Penjualan Per Kategori (Pie Chart)
        $proporsiKategori = Sale::select('kategori')
            ->selectRaw('SUM(total) as revenue')
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->get();

        // KPI Ringkasan untuk Card
        $totalRevenue = Sale::sum('total');
        $totalTransaksi = Sale::count();
        $totalUnit = Sale::sum('jumlah');

        return view('dashboard', compact(
            'trenPenjualan', 
            'penjualanPerProduk', 
            'penjualanKategoriBulanan', 
            'labelsBulan',
            'proporsiKategori',
            'totalRevenue',
            'totalTransaksi',
            'totalUnit'
        ));
    }

    /**
     * Export ke Excel menggunakan Maatwebsite Excel
     */
    public function exportExcel()
    {
        return Excel::download(new SalesExport, 'Laporan_Penjualan_411231179.xlsx');
    }

    public function exportPDF()
    {
        $sales = Sale::orderBy('id', 'asc')->get();
        $pdf = Pdf::loadView('reports.sales_pdf', compact('sales'));
        
        return $pdf->download('Laporan_Penjualan_411231179.pdf');
    }
}
