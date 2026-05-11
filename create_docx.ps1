# ============================================================
# Script: create_docx.ps1
# Purpose: Generate Data Understanding .docx via Word COM
# ============================================================

$outputPath = "C:\Users\USER\Downloads\Data_Understanding_411231179_Edisyah_Putra_Waruwu.docx"

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$doc  = $word.Documents.Add()
$sel  = $word.Selection

# ---- Helpers ----------------------------------------------------------------
function Set-Style($name) { try { $sel.Style = $doc.Styles.Item($name) } catch {} }
function Add-Heading1($t) { Set-Style "Heading 1"; $sel.TypeText($t); $sel.TypeParagraph() }
function Add-Heading2($t) { Set-Style "Heading 2"; $sel.TypeText($t); $sel.TypeParagraph() }
function Add-Heading3($t) { Set-Style "Heading 3"; $sel.TypeText($t); $sel.TypeParagraph() }
function Add-Normal($t)   { Set-Style "Normal";    $sel.TypeText($t); $sel.TypeParagraph() }
function Add-Bullet($t)   { Set-Style "List Bullet"; $sel.TypeText($t); $sel.TypeParagraph() }
function Add-Para()       { Set-Style "Normal"; $sel.TypeParagraph() }

function Add-Code($lines) {
    $sel.Font.Name = "Courier New"; $sel.Font.Size = 9
    foreach ($ln in $lines) { $sel.TypeText($ln); $sel.TypeParagraph() }
    $sel.Font.Name = "Calibri"; $sel.Font.Size = 11
}

function Add-Table($headers, $rows) {
    $nCols = $headers.Count
    $nRows = $rows.Count + 1
    $tbl = $doc.Tables.Add($sel.Range, $nRows, $nCols)
    $tbl.Style = "Table Grid"
    $tbl.Borders.Enable = $true
    # Header
    for ($c = 1; $c -le $nCols; $c++) {
        $cell = $tbl.Cell(1,$c)
        $cell.Range.Text = $headers[$c-1]
        $cell.Range.Font.Bold = $true
        $cell.Range.Font.Size = 10
        $cell.Range.Font.Color = [Microsoft.Office.Interop.Word.WdColor]::wdColorWhite
        $cell.Shading.BackgroundPatternColor = [Microsoft.Office.Interop.Word.WdColor]::wdColorDarkBlue
    }
    # Rows
    for ($r = 0; $r -lt $rows.Count; $r++) {
        for ($c = 1; $c -le $nCols; $c++) {
            $cell = $tbl.Cell($r+2,$c)
            $cell.Range.Text = $rows[$r][$c-1]
            $cell.Range.Font.Size = 10
            if (($r % 2) -eq 0) {
                $cell.Shading.BackgroundPatternColor = [Microsoft.Office.Interop.Word.WdColor]::wdColorGray05
            }
        }
    }
    $tbl.Columns.AutoFit()
    $rng = $tbl.Range
    $rng.Collapse(0)   # wdCollapseEnd
    $sel.SetRange($rng.Start, $rng.End)
    $sel.MoveDown(5,1) | Out-Null
    $sel.TypeParagraph()
}
# -----------------------------------------------------------------------------

# ========================= COVER =============================================
Set-Style "Title"
$sel.TypeText("DATA UNDERSTANDING")
$sel.TypeParagraph()
Set-Style "Subtitle"
$sel.TypeText("Dashboard Analitik Penjualan")
$sel.TypeParagraph()
Add-Para
Add-Normal "Nama Mahasiswa : Edisyah Putra Waruwu"
Add-Normal "NIM            : 411231179"
Add-Normal "Mata Kuliah    : Visualisasi Data"
Add-Normal "Dataset        : 411231179_Edisyah_Putra_Waruwu.sql"
Add-Normal "Tanggal        : Mei 2026"
$sel.InsertBreak(7)   # wdPageBreak

# ========================= 1. GAMBARAN UMUM ==================================
Add-Heading1 "1. Gambaran Umum Dataset"
Add-Normal ("Dataset ini merepresentasikan rekaman transaksi penjualan produk ritel selama periode " +
            "Januari hingga Maret 2024 (Q1 2024). Setiap baris mencatat satu transaksi penjualan " +
            "yang mencakup informasi produk, kategori, jumlah unit, harga satuan, dan total " +
            "pendapatan yang diperoleh dari transaksi tersebut.")
Add-Para

$h = @("Atribut", "Nilai")
$r = @(
    @("Nama Tabel (Raw)",            "tableName"),
    @("Nama Tabel (Ideal)",          "sales"),
    @("Total Baris (data valid)",    "300 baris"),
    @("Total Baris (termasuk kosong)","1.010 baris"),
    @("Periode Data",                "2024-01-01 s/d 2024-03-31"),
    @("Jumlah Kolom",                "7 kolom"),
    @("Format File",                 "SQL (INSERT statements)")
)
Add-Table $h $r

# ========================= 2. STRUKTUR TABEL =================================
Add-Heading1 "2. Struktur Tabel dan Tipe Data"

Add-Heading2 "2.1 Skema Asli (Raw)"
Add-Normal "Berikut adalah definisi tabel asli yang diekspor dari dataset:"
Add-Para
Add-Code @(
    "CREATE TABLE ``tableName`` (",
    "    ``id``        INT,",
    "    ``tanggal``   VARCHAR(512),",
    "    ``produk``    VARCHAR(512),",
    "    ``kategori``  VARCHAR(512),",
    "    ``jumlah``    INT,",
    "    ``harga``     INT,",
    "    ``total``     INT",
    ");"
)
Add-Para
Add-Normal ("Catatan: Tipe data asli tidak optimal. Kolom tanggal disimpan sebagai VARCHAR(512) " +
            "padahal hanya membutuhkan 10 karakter (YYYY-MM-DD). Kolom total berpotensi melebihi " +
            "batas maksimum tipe INT pada nilai transaksi yang besar seperti Laptop.")
Add-Para

Add-Heading2 "2.2 Perbandingan Tipe Data: Asli vs Ideal"
$h = @("#", "Kolom", "Tipe Asli (Raw)", "Tipe Ideal (MySQL)", "Alasan Perubahan")
$r = @(
    @("1","id",       "INT",           "BIGINT UNSIGNED AUTO_INCREMENT","Skalabilitas; PK dikelola otomatis"),
    @("2","tanggal",  "VARCHAR(512)",  "DATE (nullable)",               "Hemat storage; nullable untuk data tidak valid"),
    @("3","produk",   "VARCHAR(512)",  "VARCHAR(255)",                  "Nama produk tidak melebihi 255 karakter"),
    @("4","kategori", "VARCHAR(512)",  "VARCHAR(100)",                  "Nilai kategori pendek dan terbatas"),
    @("5","jumlah",   "INT",           "UNSIGNED INT",                  "Jumlah tidak pernah bernilai negatif"),
    @("6","harga",    "INT",           "BIGINT UNSIGNED",               "Harga Laptop Rp 8.000.000; BIGINT lebih aman"),
    @("7","total",    "INT",           "BIGINT UNSIGNED",               "Total max Rp 112.000.000; wajib BIGINT")
)
Add-Table $h $r

Add-Heading2 "2.3 Skema Ideal (Laravel Migration)"
Add-Code @(
    "Schema::create('sales', function (Blueprint `$table) {",
    "    `$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT PK",
    "    `$table->date('tanggal')->nullable();   // DATE, boleh NULL",
    "    `$table->string('produk', 255);         // VARCHAR(255)",
    "    `$table->string('kategori', 100);       // VARCHAR(100)",
    "    `$table->unsignedInteger('jumlah');     // INT UNSIGNED",
    "    `$table->unsignedBigInteger('harga');   // BIGINT UNSIGNED",
    "    `$table->unsignedBigInteger('total');   // BIGINT UNSIGNED",
    "    `$table->timestamps();",
    "});"
)
Add-Para

# ========================= 3. DEFINISI BISNIS ================================
Add-Heading1 "3. Definisi Bisnis Setiap Kolom"

Add-Heading2 "3.1  id — Identifikasi Unik Transaksi"
Add-Bullet "Definisi: Nomor urut unik yang mengidentifikasi setiap transaksi penjualan secara individual."
Add-Bullet "Peran Bisnis: Primary key — acuan saat memperbarui, menghapus, atau menghubungkan data transaksi dengan tabel lain (misal: tabel retur atau pengiriman)."
Add-Bullet "Contoh Nilai: 1, 2, 3, ..., 300"
Add-Bullet "Aturan: Tidak boleh NULL dan tidak boleh duplikat."
Add-Para

Add-Heading2 "3.2  tanggal — Tanggal Transaksi"
Add-Bullet "Definisi: Tanggal kapan transaksi penjualan terjadi, dalam format YYYY-MM-DD."
Add-Bullet "Peran Bisnis: Kolom kunci untuk analisis tren waktu — agregasi per hari, minggu, bulan, dan kuartal. Digunakan di grafik time series pada dashboard."
Add-Bullet "Contoh Nilai: 2024-01-27, 2024-03-31"
Add-Bullet "Masalah Kualitas: Terdapat nilai 'Tidak Diketahui' (string tidak valid) — perlu di-set NULL saat import."
Add-Bullet "Rentang Data: 1 Januari 2024 hingga 31 Maret 2024 (Q1 2024)"
Add-Para

Add-Heading2 "3.3  produk — Nama Produk"
Add-Bullet "Definisi: Nama barang/produk yang terjual dalam satu transaksi."
Add-Bullet "Peran Bisnis: Digunakan untuk analisis performa per produk — produk paling laris dan produk dengan pendapatan tertinggi."
Add-Bullet "Masalah Kualitas: Beberapa baris memiliki nilai 'Tidak Diketahui' yang menandakan data pencatatan tidak lengkap."
Add-Para
$h = @("Produk", "Kategori")
$r = @(
    @("Laptop",   "Elektronik"),
    @("Keyboard", "Elektronik"),
    @("Mouse",    "Elektronik"),
    @("Headset",  "Elektronik"),
    @("Tas",      "Aksesoris"),
    @("Buku",     "Edukasi"),
    @("Pulpen",   "ATK")
)
Add-Table $h $r

Add-Heading2 "3.4  kategori — Kategori Produk"
Add-Bullet "Definisi: Pengelompokan produk ke dalam segmen/kategori bisnis tertentu."
Add-Bullet "Peran Bisnis: Analisis kontribusi per segmen — kategori yang mendominasi penjualan. Berguna untuk keputusan pengadaan stok dan strategi pemasaran per segmen."
Add-Para
$h = @("Kategori", "Deskripsi", "Produk")
$r = @(
    @("Elektronik",      "Perangkat elektronik",       "Laptop, Mouse, Keyboard, Headset"),
    @("Aksesoris",       "Aksesori fashion/lifestyle", "Tas"),
    @("Edukasi",         "Produk kebutuhan belajar",   "Buku"),
    @("ATK",             "Alat Tulis Kantor",          "Pulpen"),
    @("Tidak Diketahui", "Data tidak valid",           "Perlu dibersihkan")
)
Add-Table $h $r

Add-Heading2 "3.5  jumlah — Jumlah Unit Terjual"
Add-Bullet "Definisi: Banyaknya unit produk yang terjual dalam satu transaksi."
Add-Bullet "Peran Bisnis: Mengukur volume penjualan. Berguna untuk analisis permintaan, perencanaan restock, dan identifikasi produk fast-moving vs slow-moving."
Add-Bullet "Rentang Nilai: 1 hingga 15 unit per transaksi."
Add-Para

Add-Heading2 "3.6  harga — Harga Satuan (Rp)"
Add-Bullet "Definisi: Harga jual per unit produk dalam satuan Rupiah (IDR) pada saat transaksi terjadi."
Add-Bullet "Peran Bisnis: Digunakan untuk menghitung pendapatan, analisis margin, dan perbandingan harga antar produk. Harga bersifat tetap per produk dalam dataset ini."
Add-Para
$h = @("Produk", "Harga Satuan")
$r = @(
    @("Laptop",   "Rp 8.000.000"),
    @("Headset",  "Rp 300.000"),
    @("Keyboard", "Rp 250.000"),
    @("Tas",      "Rp 200.000"),
    @("Mouse",    "Rp 100.000"),
    @("Buku",     "Rp 75.000"),
    @("Pulpen",   "Rp 5.000")
)
Add-Table $h $r

Add-Heading2 "3.7  total — Total Pendapatan Transaksi (Rp)"
Add-Bullet "Definisi: Nilai total pendapatan dari satu transaksi, dihitung sebagai jumlah x harga."
Add-Bullet "Peran Bisnis: Metrik utama dalam dashboard analitik — total pendapatan harian/bulanan, perbandingan performa antar produk/kategori, dan KPI utama (Total Revenue, Average Order Value)."
Add-Bullet "Nilai Minimum: Rp 5.000 (1 Pulpen x Rp 5.000)"
Add-Bullet "Nilai Maksimum: Rp 112.000.000 (14 Laptop x Rp 8.000.000)"
Add-Bullet "Penting: Nilai ini melebihi kapasitas aman INT, sehingga wajib menggunakan BIGINT UNSIGNED."
Add-Para

# ========================= 4. KUALITAS DATA ==================================
Add-Heading1 "4. Identifikasi Masalah Kualitas Data"
$h = @("#","Masalah","Kolom Terdampak","Est. Baris","Rekomendasi")
$r = @(
    @("1","Nilai 'Tidak Diketahui'","tanggal, produk, kategori","~10 baris","Set NULL atau tandai dengan flag is_valid"),
    @("2","Baris kosong (semua '')","Semua kolom","~710 baris","Hapus sebelum import ke database"),
    @("3","VARCHAR untuk tanggal","tanggal","Semua baris","Konversi ke tipe DATE"),
    @("4","Tidak ada PRIMARY KEY","id","Semua baris","Tambahkan constraint PK AUTO_INCREMENT"),
    @("5","INT terlalu kecil","total","Beberapa baris","Ubah ke BIGINT UNSIGNED")
)
Add-Table $h $r

# ========================= 5. STATISTIK ======================================
Add-Heading1 "5. Ringkasan Statistik Data"

Add-Heading2 "5.1 Rentang Nilai Numerik"
$h = @("Kolom","Nilai Minimum","Nilai Maksimum","Catatan")
$r = @(
    @("jumlah","1 unit","15 unit","Distribusi merata 1-15 unit per transaksi"),
    @("harga","Rp 5.000","Rp 8.000.000","Rentang sangat lebar (Pulpen vs Laptop)"),
    @("total","Rp 5.000","Rp 112.000.000","Berbanding lurus dengan jumlah x harga")
)
Add-Table $h $r

Add-Heading2 "5.2 Distribusi Kategori"
Add-Bullet "Elektronik    : Laptop, Keyboard, Mouse, Headset (kategori dominan — 4 produk)"
Add-Bullet "ATK           : Pulpen"
Add-Bullet "Aksesoris     : Tas"
Add-Bullet "Edukasi       : Buku"
Add-Bullet "Tidak Diketahui : Data tidak valid — perlu dibersihkan sebelum analisis"
Add-Para

# ========================= 6. QUERY ANALITIK =================================
Add-Heading1 "6. Contoh Query Analitik untuk Dashboard"
Add-Normal "Berikut contoh query SQL yang dapat digunakan langsung setelah data berhasil diimport:"
Add-Para
Add-Code @(
    "-- 1. Total pendapatan keseluruhan",
    "SELECT SUM(total) AS total_revenue FROM sales WHERE tanggal IS NOT NULL;",
    "",
    "-- 2. Pendapatan per kategori",
    "SELECT kategori, SUM(total) AS revenue",
    "FROM sales WHERE tanggal IS NOT NULL",
    "GROUP BY kategori ORDER BY revenue DESC;",
    "",
    "-- 3. Tren penjualan bulanan",
    "SELECT MONTH(tanggal) AS bulan, MONTHNAME(tanggal) AS nama_bulan,",
    "       SUM(total) AS revenue, SUM(jumlah) AS total_unit",
    "FROM sales WHERE tanggal IS NOT NULL",
    "GROUP BY MONTH(tanggal) ORDER BY bulan;",
    "",
    "-- 4. Produk terlaris berdasarkan unit terjual",
    "SELECT produk, SUM(jumlah) AS total_unit, SUM(total) AS total_revenue",
    "FROM sales WHERE produk != 'Tidak Diketahui'",
    "GROUP BY produk ORDER BY total_unit DESC;"
)

# ========================= SAVE ==============================================
$doc.SaveAs([ref]$outputPath, [ref]16)
$doc.Close()
$word.Quit()

[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()

Write-Host ""
Write-Host "SUCCESS: File disimpan di:" -ForegroundColor Green
Write-Host "  $outputPath" -ForegroundColor Cyan
