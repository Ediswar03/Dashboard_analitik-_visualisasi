<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Sale::orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'ID Transaksi', 'Tanggal', 'Produk', 'Kategori', 'Jumlah', 'Harga', 'Total'
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->tanggal ? $sale->tanggal->format('d/m/Y') : '-',
            $sale->produk,
            $sale->kategori,
            $sale->jumlah,
            $sale->harga,
            $sale->total,
        ];
    }
}
