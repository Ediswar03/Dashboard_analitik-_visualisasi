<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;

// Dashboard utama
Route::get('/', [SalesController::class, 'dashboard'])->name('dashboard');

// Export Data
Route::get('/export/excel', [SalesController::class, 'exportExcel'])->name('sales.excel');
Route::get('/export/pdf', [SalesController::class, 'exportPDF'])->name('sales.pdf');
