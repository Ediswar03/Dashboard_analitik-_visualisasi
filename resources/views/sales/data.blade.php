<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan - SalesAnalytix</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #6366f1;
            --dark: #1e1b4b;
            --light: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            min-height: 100vh;
        }

        .navbar {
            background: #1e1b4b !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .glass-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: none;
        }

        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            color: #64748b;
            border-top: none;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 8px !important;
            margin: 0 3px;
            color: var(--primary);
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
        }

        .btn-outline-secondary {
            border-radius: 10px;
        }

        .form-select, .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }

        .badge-custom {
            padding: 0.5em 0.8em;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <i class="bi bi-graph-up-arrow me-2 text-primary fs-3"></i>
            <span class="fw-bold text-white">SalesAnalytix</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="/data"><i class="bi bi-table me-1"></i> Data Penjualan</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dataset Penjualan</h2>
            <p class="text-muted">Manajemen dan filter data transaksi lengkap</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary rounded-pill px-3 py-2">Total: {{ $sales->total() }} Data</span>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form action="/data" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Cari Produk</label>
                <select name="produk" class="form-select">
                    <option value="">Semua Produk</option>
                    @foreach($produkList as $p)
                        <option value="{{ $p }}" {{ request('produk') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    <option value="1" {{ request('bulan') == '1' ? 'selected' : '' }}>Januari</option>
                    <option value="2" {{ request('bulan') == '2' ? 'selected' : '' }}>Februari</option>
                    <option value="3" {{ request('bulan') == '3' ? 'selected' : '' }}>Maret</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2 flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
                <a href="/data" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end pe-4">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="ps-4 text-muted small">#{{ $sale->id }}</td>
                        <td>{{ $sale->tanggal ? $sale->tanggal->format('d M Y') : '-' }}</td>
                        <td class="fw-bold">{{ $sale->produk }}</td>
                        <td><span class="badge bg-primary-subtle text-primary badge-custom">{{ $sale->kategori }}</span></td>
                        <td class="text-center">{{ $sale->jumlah }}</td>
                        <td class="text-end">Rp {{ number_format($sale->harga, 0, ',', '.') }}</td>
                        <td class="text-end pe-4 fw-bold text-primary">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                            Tidak ada data yang ditemukan untuk kriteria filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 bg-light border-top d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $sales->firstItem() ?? 0 }} sampai {{ $sales->lastItem() ?? 0 }} dari {{ $sales->total() }} data
            </div>
            <div>
                {{ $sales->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <footer class="mt-5 py-4 text-center text-muted border-top">
        <p>&copy; 2024 Dashboard Analitik Penjualan - Edisyah Putra Waruwu (411231179)</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
