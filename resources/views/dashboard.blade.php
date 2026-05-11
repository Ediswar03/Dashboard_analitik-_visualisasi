<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytix Dashboard - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #6366f1;
            --dark: #1e1b4b;
            --light: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body { font-family: 'Outfit', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .glass-card { background: white; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .glass-card:hover { transform: translateY(-5px); }
        .kpi-card { background: linear-gradient(135deg, #4f46e5, #6366f1); color: white; }
        .chart-container { position: relative; height: 320px; width: 100%; }
        .navbar { background: #fff !important; border-bottom: 1px solid #e2e8f0; }

        /* Dropdown Hover Effect */
        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item { padding: 10px 20px; font-weight: 500; border-radius: 8px; }
        .dropdown-item:hover { background-color: #f1f5f9; color: var(--primary); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
            <i class="bi bi-bar-chart-steps text-primary fs-3 me-2"></i> SalesAnalytix
        </a>
        <div class="ms-auto">
            <!-- Single Dropdown Export -->
            <div class="dropdown d-inline-block">
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold" type="button">
                    <i class="bi bi-download me-2"></i> Export Data
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 p-2 shadow">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('sales.excel') }}">
                            <i class="bi bi-file-earmark-excel text-success me-2 fs-5"></i> Excel Report (.xlsx)
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('sales.pdf') }}">
                            <i class="bi bi-file-earmark-pdf text-danger me-2 fs-5"></i> PDF Document (.pdf)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1">Analitik Penjualan</h2>
            <p class="text-muted">Transformasi & Analisis Data Penjualan Q1 2024</p>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-3">
            <div class="glass-card p-3 px-4 text-center">
                <small class="text-muted d-block">Total Revenue</small>
                <span class="fw-bold text-primary fs-4">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="glass-card p-3 px-4 text-center">
                <small class="text-muted d-block">Transaksi</small>
                <span class="fw-bold text-success fs-4">{{ number_format($totalTransaksi, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- 1. Line Chart: Tren Penjualan -->
        <div class="col-md-7">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Tren Penjualan Berdasarkan Waktu</h6>
                    <i class="bi bi-graph-up text-primary"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </div>

        <!-- 4. Pie Chart: Proporsi Kategori -->
        <div class="col-md-5">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Proporsi Penjualan Per Kategori</h6>
                    <i class="bi bi-pie-chart-fill text-danger"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chartProporsi"></canvas>
                </div>
            </div>
        </div>

        <!-- 2. Bar Chart: Total Penjualan Per Produk -->
        <div class="col-md-6">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Total Penjualan Per Produk</h6>
                    <i class="bi bi-box-seam text-success"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chartProduk"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. Bar Chart: Penjualan Per Kategori Per Bulan -->
        <div class="col-md-6">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">Penjualan Per Kategori Per Bulan</h6>
                    <i class="bi bi-calendar3 text-warning"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chartKategoriBulanan"></canvas>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 pt-4 text-center border-top">
        <p class="text-muted small">&copy; 2024 UTS Visualisasi Data - Edisyah Putra Waruwu (411231179)</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Konfigurasi Chart.js
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Line Chart: Tren
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: @json($trenPenjualan->pluck('tgl')),
            datasets: [{
                label: 'Revenue (Rp)',
                data: @json($trenPenjualan->pluck('total_revenue')),
                borderColor: '#4f46e5',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(79, 70, 229, 0.05)',
                pointRadius: 0,
                pointHoverRadius: 6
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Bar Chart: Produk
    new Chart(document.getElementById('chartProduk'), {
        type: 'bar',
        data: {
            labels: @json($penjualanPerProduk->pluck('produk')),
            datasets: [{
                label: 'Total Revenue',
                data: @json($penjualanPerProduk->pluck('total_revenue')),
                backgroundColor: '#10b981',
                borderRadius: 10,
                maxBarThickness: 40
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
        }
    });

    // 3. Grouped Bar Chart: Kategori per Bulan
    new Chart(document.getElementById('chartKategoriBulanan'), {
        type: 'bar',
        data: {
            labels: @json($labelsBulan),
            datasets: [
                @foreach($penjualanKategoriBulanan as $item)
                {
                    label: '{{ $item["kategori"] }}',
                    data: @json($item["data"]),
                    backgroundColor: '{{ $item["kategori"] == "Elektronik" ? "#4f46e5" : ($item["kategori"] == "Aksesoris" ? "#10b981" : "#f59e0b") }}',
                    borderRadius: 5
                },
                @endforeach
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { y: { grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
        }
    });

    // 4. Pie Chart: Proporsi
    new Chart(document.getElementById('chartProporsi'), {
        type: 'pie',
        data: {
            labels: @json($proporsiKategori->pluck('kategori')),
            datasets: [{
                data: @json($proporsiKategori->pluck('revenue')),
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9'],
                borderWidth: 0
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
        }
    });
</script>
</body>
</html>
