<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class CleanSalesData extends Command
{
    /**
     * Nama command artisan.
     * Jalankan dengan: php artisan sales:clean
     */
    protected $signature   = 'sales:clean {--dry-run : Tampilkan preview tanpa mengubah data}';
    protected $description = 'Membersihkan data pada tabel sales: NULL, tanggal invalid, title case, dan recalculate total';

    /**
     * Mapping produk → kategori yang benar (ground truth)
     */
    private array $produkKategori = [
        'laptop'   => ['produk' => 'Laptop',   'kategori' => 'Elektronik'],
        'keyboard' => ['produk' => 'Keyboard', 'kategori' => 'Elektronik'],
        'mouse'    => ['produk' => 'Mouse',    'kategori' => 'Elektronik'],
        'headset'  => ['produk' => 'Headset',  'kategori' => 'Elektronik'],
        'tas'      => ['produk' => 'Tas',      'kategori' => 'Aksesoris'],
        'buku'     => ['produk' => 'Buku',     'kategori' => 'Edukasi'],
        'pulpen'   => ['produk' => 'Pulpen',   'kategori' => 'ATK'],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║      DATA CLEANING — Tabel Sales         ║');
        $this->info('║      411231179_Edisyah_Putra_Waruwu      ║');
        $this->info('╚══════════════════════════════════════════╝');

        if ($isDryRun) {
            $this->warn('  [DRY RUN] Tidak ada perubahan yang disimpan.');
        }

        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 0 — Laporan kondisi SEBELUM cleaning
        // ──────────────────────────────────────────────────────────────
        $this->line('📊 <fg=cyan>KONDISI DATA SEBELUM CLEANING</>');
        $this->reportStats();
        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 1 — Hapus baris benar-benar kosong
        // ──────────────────────────────────────────────────────────────
        $this->line('🗑️  <fg=yellow>STEP 1: Hapus baris kosong / placeholder</>');

        $emptyRows = Sale::where(function ($q) {
                $q->whereNull('produk')->orWhere('produk', '');
            })
            ->where(function ($q) {
                $q->whereNull('kategori')->orWhere('kategori', '');
            })
            ->where(function ($q) {
                $q->whereNull('jumlah')->orWhere('jumlah', 0);
            })
            ->where(function ($q) {
                $q->whereNull('harga')->orWhere('harga', 0);
            })
            ->get();

        $this->line("  → Ditemukan <fg=red>{$emptyRows->count()}</> baris kosong");

        if (! $isDryRun && $emptyRows->count() > 0) {
            Sale::whereIn('id', $emptyRows->pluck('id'))->delete();
            $this->line("  ✅ <fg=green>{$emptyRows->count()} baris berhasil dihapus</>");
        }
        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 2 — Perbaiki tanggal tidak valid & Imputasi Data
        // ──────────────────────────────────────────────────────────────
        $this->line('📅 <fg=yellow>STEP 2: Perbaiki tanggal & Imputasi Data (Pengisian Otomatis)</>');

        // 2a. Bersihkan teks non-tanggal dulu dengan cara yang aman
        DB::table('sales')->whereRaw("tanggal = 'Tidak Diketahui' OR tanggal = ''")->update(['tanggal' => null]);

        // 2b. Logika Imputasi: Cari yang NULL dan isi dari data sebelumnya
        $nullDates = Sale::whereNull('tanggal')->orderBy('id', 'asc')->get();
        $fixedDates = 0;

        foreach ($nullDates as $sale) {
            // Cari data sebelumnya yang punya tanggal (ID < ID sekarang)
            $previous = Sale::where('id', '<', $sale->id)
                ->whereNotNull('tanggal')
                ->orderBy('id', 'desc')
                ->first();

            if ($previous) {
                $sale->tanggal = $previous->tanggal;
                if (! $isDryRun) { $sale->save(); }
                $fixedDates++;
            } else {
                // Jika tidak ada data sebelumnya (baris pertama), cari data sesudahnya
                $next = Sale::where('id', '>', $sale->id)
                    ->whereNotNull('tanggal')
                    ->orderBy('id', 'asc')
                    ->first();
                
                if ($next) {
                    $sale->tanggal = $next->tanggal;
                    if (! $isDryRun) { $sale->save(); }
                    $fixedDates++;
                }
            }
        }

        $this->line("  → Berhasil memprediksi dan mengisi <fg=green>{$fixedDates}</> tanggal yang kosong.");
        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 3 — Seragamkan produk dan kategori (Title Case + mapping)
        // ──────────────────────────────────────────────────────────────
        $this->line('🏷️  <fg=yellow>STEP 3: Seragamkan produk dan kategori (Title Case)</>');

        $allSales = Sale::whereNotNull('id')->get();
        $fixedProduk    = 0;
        $fixedKategori  = 0;
        $nullifiedProduk = 0;

        foreach ($allSales as $sale) {
            $changed  = false;
            $lowerProduk = strtolower(trim($sale->produk ?? ''));

            // -- produk: Tidak Diketahui atau kosong → NULL
            if (in_array($lowerProduk, ['tidak diketahui', '']) || $sale->produk === null) {
                if ($sale->produk !== null) {
                    $sale->produk    = null;
                    $sale->kategori  = null;
                    $changed         = true;
                    $nullifiedProduk++;
                }
            }
            // -- produk yang diketahui → title case + perbaiki kategori
            elseif (isset($this->produkKategori[$lowerProduk])) {
                $map = $this->produkKategori[$lowerProduk];

                if ($sale->produk !== $map['produk']) {
                    $sale->produk = $map['produk'];
                    $changed = true;
                    $fixedProduk++;
                }

                if ($sale->kategori !== $map['kategori']) {
                    $sale->kategori = $map['kategori'];
                    $changed = true;
                    $fixedKategori++;
                }
            }

            // -- kategori: Tidak Diketahui → NULL (jika produk juga tidak diketahui)
            if (
                in_array(strtolower(trim($sale->kategori ?? '')), ['tidak diketahui', ''])
                && $sale->produk === null
            ) {
                $sale->kategori = null;
                $changed = true;
            }

            if ($changed && ! $isDryRun) {
                $sale->save();
            }
        }

        $this->line("  → <fg=green>{$fixedProduk}</> nama produk diperbaiki ke Title Case");
        $this->line("  → <fg=green>{$fixedKategori}</> kategori diperbaiki / dikoreksi");
        $this->line("  → <fg=red>{$nullifiedProduk}</> produk 'Tidak Diketahui' diubah ke NULL");
        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 4 — Recalculate total = jumlah * harga
        // ──────────────────────────────────────────────────────────────
        $this->line('💰 <fg=yellow>STEP 4: Recalculate total = jumlah × harga</>');

        $mismatch = Sale::whereNotNull('jumlah')
            ->whereNotNull('harga')
            ->where('jumlah', '>', 0)
            ->where('harga',  '>', 0)
            ->whereRaw('total != (jumlah * harga)')
            ->get();

        $this->line("  → Ditemukan <fg=red>{$mismatch->count()}</> baris dengan total tidak sesuai");

        if (! $isDryRun) {
            $updated = DB::update(
                'UPDATE sales SET total = jumlah * harga WHERE jumlah IS NOT NULL AND harga IS NOT NULL AND jumlah > 0 AND harga > 0'
            );
            $this->line("  ✅ <fg=green>{$updated} baris</> total berhasil diperbarui");
        }
        $this->info('');

        // ──────────────────────────────────────────────────────────────
        // STEP 5 — Laporan kondisi SETELAH cleaning
        // ──────────────────────────────────────────────────────────────
        if (! $isDryRun) {
            $this->line('📊 <fg=cyan>KONDISI DATA SETELAH CLEANING</>');
            $this->reportStats();
        }

        $this->info('');
        $this->info('✅ <fg=green>Data cleaning selesai!</>');
        $this->info('');

        return Command::SUCCESS;
    }

    /**
     * Tampilkan laporan statistik kondisi data saat ini.
     */
    private function reportStats(): void
    {
        $total       = Sale::count();
        $nullTanggal = Sale::whereNull('tanggal')->count();
        $nullProduk  = Sale::whereNull('produk')->count();
        $nullKategori= Sale::whereNull('kategori')->count();
        $mismatch    = Sale::whereRaw('total != (jumlah * harga)')
                           ->whereNotNull('jumlah')
                           ->whereNotNull('harga')
                           ->count();

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total baris',              $total],
                ['tanggal IS NULL',          $nullTanggal],
                ['produk IS NULL',           $nullProduk],
                ['kategori IS NULL',         $nullKategori],
                ['total != jumlah × harga',  $mismatch],
            ]
        );
    }
}
