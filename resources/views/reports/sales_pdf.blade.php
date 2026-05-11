<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Resmi - Q1 2024</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; }
        .header h1 { color: #4f46e5; margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 2px 0; border: none; font-size: 11px; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        
        /* CSS agar header TIDAK mengulang di setiap halaman */
        table.main-table thead { display: table-row-group; } 

        table.main-table th { 
            background-color: #4f46e5; 
            color: white; 
            padding: 10px 8px; 
            text-align: left; 
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        table.main-table td { 
            padding: 8px; 
            border-bottom: 1px solid #e2e8f0; 
            word-wrap: break-word;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; } /* Mencegah teks angka turun ke bawah */
        
        .badge { 
            padding: 3px 6px; 
            border-radius: 4px; 
            font-size: 8px; 
            background-color: #f1f5f9; 
            color: #475569; 
        }

        /* Perbaikan Gaya Grand Total */
        .total-row { background-color: #f8fafc; }
        .total-label { padding: 15px !important; text-align: right; font-size: 11px; color: #1e293b; }
        .total-amount { 
            padding: 15px !important; 
            text-align: right; 
            font-size: 12px; 
            color: #4f46e5; 
            background-color: #eef2ff;
        }

        .footer { 
            position: fixed; 
            bottom: 30px; 
            left: 30px; 
            right: 30px; 
            font-size: 8px; 
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Analitik Penjualan</h1>
            <p>Dashboard Monitoring Q1 2024 - SalesAnalytix</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="150"><strong>Nama Mahasiswa</strong></td>
                <td>: Edisyah Putra Waruwu (411231179)</td>
                <td width="100" class="text-right"><strong>Tgl Cetak</strong></td>
                <td width="120">: {{ now()->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Dataset</strong></td>
                <td>: 411231179_Edisyah_Putra_Waruwu.sql</td>
                <td class="text-right"><strong>Total Item</strong></td>
                <td>: {{ $sales->count() }} Transaksi</td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th width="35">ID</th>
                    <th width="75">Tanggal</th>
                    <th>Nama Produk</th>
                    <th width="90">Kategori</th>
                    <th width="45" class="text-center">Unit</th>
                    <th width="85" class="text-right">Harga (Rp)</th>
                    <th width="95" class="text-right">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($sales as $sale)
                <tr>
                    <td class="text-center fw-bold">{{ $sale->id }}</td>
                    <td class="text-muted">
                        {{ $sale->tanggal->format('d/m/Y') }}
                    </td>
                    <td>{{ $sale->produk ?? 'Produk Tidak Terdaftar' }}</td>
                    <td><span class="badge">{{ $sale->kategori ?? 'Uncategorized' }}</span></td>
                    <td class="text-center">{{ $sale->jumlah }}</td>
                    <td class="text-right nowrap">{{ number_format($sale->harga, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold nowrap">{{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $sale->total; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="total-label fw-bold">GRAND TOTAL PENDAPATAN</td>
                    <td class="total-amount fw-bold nowrap">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Dokumen ini dihasilkan secara otomatis oleh sistem SalesAnalytix. Seluruh data telah melalui proses validasi dan transformasi.
        </div>
    </div>
</body>
</html>
