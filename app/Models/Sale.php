<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini.
     * Laravel secara default akan mencari 'sales' (plural dari 'Sale'),
     * tapi kita definisikan eksplisit agar jelas.
     */
    protected $table = 'sales';

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'tanggal',
        'produk',
        'kategori',
        'jumlah',
        'harga',
        'total',
    ];

    /**
     * Cast tipe data kolom secara otomatis.
     */
    protected $casts = [
        'tanggal'  => 'date',
        'jumlah'   => 'integer',
        'harga'    => 'integer',
        'total'    => 'integer',
    ];
}
