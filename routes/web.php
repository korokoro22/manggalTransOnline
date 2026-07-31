<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangKeluarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataBusController;
use App\Http\Controllers\LaporanBarangController;
use App\Http\Controllers\LaporanBusController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\NotaJalanController;
use App\Http\Controllers\PaketServiceController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('master-barang', MasterBarangController::class);
    Route::get('master-barang/nama/{nama_barang}', [MasterBarangController::class, 'showByNama'])
    ->name('master-barang.by-nama');
    Route::get('master-barang/nama/{nama_barang}/export-pdf', [MasterBarangController::class, 'exportPdfByNama'])
    ->name('master-barang.export-pdf-show-by-nama');
    Route::get('master-barang-export', [MasterBarangController::class, 'exportPdf'])->name('master-barang.export-pdf');
    Route::get('master-barang-export/{id}', [MasterBarangController::class, 'exportPdfShow'])->name('master-barang.export-pdf-show');
    Route::get('master-barang-json/{id}', [MasterBarangController::class, 'getJson'])->name('master-barang.json');

    Route::resource('barang-masuk', BarangMasukController::class);
    Route::get('barang-masuk-export', [BarangMasukController::class, 'exportPdf'])->name('barang-masuk.export-pdf');
    Route::get('barang-masuk-export/{id}', [BarangMasukController::class, 'exportPdfShow'])->name('barang-masuk.export-pdf-show');

    Route::resource('barang-keluar', BarangKeluarController::class);
    Route::get('barang-keluar-export-pdf', [BarangKeluarController::class, 'exportPdf'])->name('barang-keluar.export-pdf');
    Route::get('barang-keluar-export-pdf/{id}', [BarangKeluarController::class, 'exportPdfShow'])->name('barang-keluar.export-pdf-show');
    Route::get('barang-keluar/paket-by-bus/{busId}', [BarangKeluarController::class, 'getPaketByBus'])->name('barang-keluar.paket-by-bus');

    Route::resource('nota-jalan', NotaJalanController::class);
    Route::get('nota-jalan-export', [NotaJalanController::class, 'exportPdf'])->name('nota-jalan.export-pdf');
    Route::get('nota-jalan-export/{id}', [NotaJalanController::class, 'exportPdfShow'])->name('nota-jalan.export-pdf-show');
    
    Route::resource('data-bus', DataBusController::class);
    Route::get('data-bus-export', [DataBusController::class, 'exportPdf'])->name('data-bus.export-pdf');
    Route::get('data-bus-export/{id}', [DataBusController::class, 'exportPdfShow'])->name('data-bus.export-pdf-show');

    Route::get('laporan-bus', [LaporanBusController::class, 'index'])->name('laporan-bus.index');
    Route::get('laporan-bus-export', [LaporanBusController::class, 'exportPdf'])->name('laporan-bus.export-pdf');
    Route::get('laporan-bus/{id}', [LaporanBusController::class, 'show'])->name('laporan-bus.show');
    Route::get('laporan-bus/{id}/export', [LaporanBusController::class, 'exportPdfShow'])->name('laporan-bus.export-pdf-show');

    Route::resource('paket-service', PaketServiceController::class);
    Route::get('paket-service-export', [PaketServiceController::class, 'exportPdf'])->name('paket-service.export-pdf');
    Route::get('paket-service-export/{id}', [PaketServiceController::class, 'exportPdfShow'])->name('paket-service.export-pdf-show');


    Route::get('laporan-barang', [LaporanBarangController::class, 'index'])->name('laporan-barang.index');
Route::get('laporan-barang/export-pdf', [LaporanBarangController::class, 'exportPdf'])->name('laporan-barang.export-pdf');
});