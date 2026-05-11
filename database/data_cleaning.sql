-- ============================================================
-- DATA CLEANING — Tabel 'sales'
-- Dataset: 411231179_Edisyah_Putra_Waruwu
-- Urutan eksekusi: jalankan step 1 → 2 → 3 → 4 → 5 → 6
-- ============================================================

-- ============================================================
-- STEP 0: Cek kondisi data sebelum cleaning
-- ============================================================
SELECT
    COUNT(*)                                        AS total_rows,
    SUM(CASE WHEN tanggal IS NULL
              OR tanggal = '' 
              OR tanggal = 'Tidak Diketahui' THEN 1 ELSE 0 END) AS tanggal_invalid,
    SUM(CASE WHEN produk IS NULL
              OR produk = ''
              OR produk = 'Tidak Diketahui'  THEN 1 ELSE 0 END) AS produk_invalid,
    SUM(CASE WHEN kategori IS NULL
              OR kategori = ''
              OR kategori = 'Tidak Diketahui' THEN 1 ELSE 0 END) AS kategori_invalid,
    SUM(CASE WHEN jumlah IS NULL OR jumlah = 0 THEN 1 ELSE 0 END) AS jumlah_invalid,
    SUM(CASE WHEN harga IS NULL  OR harga = 0  THEN 1 ELSE 0 END) AS harga_invalid,
    SUM(CASE WHEN total IS NULL  OR total = 0  THEN 1 ELSE 0 END) AS total_invalid,
    SUM(CASE WHEN total != (jumlah * harga)    THEN 1 ELSE 0 END) AS total_mismatch
FROM sales;


-- ============================================================
-- STEP 1: Hapus baris benar-benar kosong / tidak valid
--         (id NULL atau kosong = baris placeholder dari SQL export)
-- ============================================================
DELETE FROM sales
WHERE id IS NULL
   OR id = 0
   OR (
       (tanggal  IS NULL OR tanggal  = '') AND
       (produk   IS NULL OR produk   = '') AND
       (kategori IS NULL OR kategori = '') AND
       (jumlah   IS NULL OR jumlah   = 0) AND
       (harga    IS NULL OR harga    = 0)
   );

-- Hasil: hanya baris dengan id valid yang tersisa (~300 baris)


-- ============================================================
-- STEP 2: Perbaiki format tanggal tidak valid
--         Nilai 'Tidak Diketahui' → NULL (tidak bisa dipaksa jadi DATE)
-- ============================================================

-- 2a. Set NULL untuk tanggal yang tidak bisa di-parse sebagai DATE
UPDATE sales
SET tanggal = NULL
WHERE tanggal = 'Tidak Diketahui'
   OR tanggal = ''
   OR tanggal IS NULL;

-- 2b. Pastikan format tanggal yang valid sudah benar (YYYY-MM-DD)
--     Jika ada format DD/MM/YYYY atau MM-DD-YYYY, konversi di sini:
UPDATE sales
SET tanggal = STR_TO_DATE(tanggal, '%d/%m/%Y')
WHERE tanggal REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$';

UPDATE sales
SET tanggal = STR_TO_DATE(tanggal, '%m-%d-%Y')
WHERE tanggal REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$'
  AND MONTH(STR_TO_DATE(tanggal, '%m-%d-%Y')) BETWEEN 1 AND 12;

-- Cek hasil:
SELECT id, tanggal FROM sales WHERE tanggal IS NULL;


-- ============================================================
-- STEP 3: Seragamkan kolom 'produk' ke Title Case
--         dan ganti nilai 'Tidak Diketahui' → NULL
-- ============================================================

-- 3a. Ganti 'Tidak Diketahui' → NULL
UPDATE sales SET produk = NULL
WHERE produk = 'Tidak Diketahui' OR produk = '' OR produk IS NULL;

-- 3b. Title Case manual untuk nilai produk yang diketahui
--     (MySQL tidak punya fungsi INITCAP/PROPER secara native)
UPDATE sales SET produk = 'Laptop'   WHERE LOWER(TRIM(produk)) = 'laptop';
UPDATE sales SET produk = 'Keyboard' WHERE LOWER(TRIM(produk)) = 'keyboard';
UPDATE sales SET produk = 'Mouse'    WHERE LOWER(TRIM(produk)) = 'mouse';
UPDATE sales SET produk = 'Headset'  WHERE LOWER(TRIM(produk)) = 'headset';
UPDATE sales SET produk = 'Tas'      WHERE LOWER(TRIM(produk)) = 'tas';
UPDATE sales SET produk = 'Buku'     WHERE LOWER(TRIM(produk)) = 'buku';
UPDATE sales SET produk = 'Pulpen'   WHERE LOWER(TRIM(produk)) = 'pulpen';

-- 3c. Hapus spasi berlebih di semua nilai produk
UPDATE sales SET produk = TRIM(produk) WHERE produk IS NOT NULL;


-- ============================================================
-- STEP 4: Seragamkan kolom 'kategori' ke Title Case
--         dan ganti nilai 'Tidak Diketahui' → NULL
-- ============================================================

-- 4a. Ganti 'Tidak Diketahui' → NULL
UPDATE sales SET kategori = NULL
WHERE kategori = 'Tidak Diketahui' OR kategori = '' OR kategori IS NULL;

-- 4b. Title Case untuk kategori
UPDATE sales SET kategori = 'Elektronik' WHERE LOWER(TRIM(kategori)) = 'elektronik';
UPDATE sales SET kategori = 'Aksesoris'  WHERE LOWER(TRIM(kategori)) = 'aksesoris';
UPDATE sales SET kategori = 'Edukasi'    WHERE LOWER(TRIM(kategori)) = 'edukasi';
UPDATE sales SET kategori = 'ATK'        WHERE LOWER(TRIM(kategori)) IN ('atk', 'a.t.k');

-- 4c. Hapus spasi berlebih
UPDATE sales SET kategori = TRIM(kategori) WHERE kategori IS NOT NULL;


-- ============================================================
-- STEP 5: Perbaiki kategori yang tidak cocok dengan produknya
--         (cross-reference produk → kategori yang benar)
-- ============================================================
UPDATE sales
SET kategori = CASE
    WHEN produk = 'Laptop'   THEN 'Elektronik'
    WHEN produk = 'Keyboard' THEN 'Elektronik'
    WHEN produk = 'Mouse'    THEN 'Elektronik'
    WHEN produk = 'Headset'  THEN 'Elektronik'
    WHEN produk = 'Tas'      THEN 'Aksesoris'
    WHEN produk = 'Buku'     THEN 'Edukasi'
    WHEN produk = 'Pulpen'   THEN 'ATK'
    ELSE kategori   -- biarkan NULL jika produk tidak diketahui
END
WHERE produk IS NOT NULL;


-- ============================================================
-- STEP 6: Update kolom 'total' = jumlah * harga
--         (recalculate semua agar konsisten)
-- ============================================================
UPDATE sales
SET total = jumlah * harga
WHERE jumlah IS NOT NULL
  AND harga  IS NOT NULL
  AND jumlah > 0
  AND harga  > 0;

-- Verifikasi: tidak ada lagi mismatch
SELECT COUNT(*) AS masih_mismatch
FROM sales
WHERE total != (jumlah * harga)
  AND jumlah IS NOT NULL
  AND harga  IS NOT NULL;


-- ============================================================
-- STEP 7 (Opsional): Hapus baris di mana data inti masih NULL
--         setelah semua cleaning selesai
-- ============================================================
-- Uncomment baris berikut jika ingin hard-delete baris tidak lengkap:
/*
DELETE FROM sales
WHERE produk IS NULL
   OR kategori IS NULL
   OR tanggal IS NULL;
*/

-- Atau: tandai saja dengan kolom flag (lebih aman untuk audit)
-- ALTER TABLE sales ADD COLUMN is_valid TINYINT(1) DEFAULT 1;
-- UPDATE sales SET is_valid = 0 WHERE produk IS NULL OR kategori IS NULL OR tanggal IS NULL;


-- ============================================================
-- STEP 8: Cek kondisi data SETELAH cleaning
-- ============================================================
SELECT
    COUNT(*)                                         AS total_rows,
    SUM(CASE WHEN tanggal  IS NULL THEN 1 ELSE 0 END) AS tanggal_null,
    SUM(CASE WHEN produk   IS NULL THEN 1 ELSE 0 END) AS produk_null,
    SUM(CASE WHEN kategori IS NULL THEN 1 ELSE 0 END) AS kategori_null,
    SUM(CASE WHEN total != (jumlah * harga) THEN 1 ELSE 0 END) AS total_mismatch
FROM sales;
