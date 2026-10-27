<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GuruPendidikController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IndikatorKinerjaController;
use App\Http\Controllers\LaporanKinerjaController;
use App\Http\Controllers\PartisipasiSiswaController;
use App\Http\Controllers\PetaSebaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RenstraAkipController;
use App\Http\Controllers\SatuanPendidikanController;
use App\Http\Controllers\TujuanDuaController;
use App\Http\Controllers\TujuanSatuController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/captcha/refresh', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh')->middleware('throttle:30,1');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('menu:dashboard');

    // Indikator Kinerja Hasil
    Route::prefix('indikator')->name('indikator.')->group(function () {
        Route::get('/apk', [IndikatorKinerjaController::class, 'apk'])->name('apk')->middleware('menu:indikator_apk');
        Route::get('/apm', [IndikatorKinerjaController::class, 'apm'])->name('apm')->middleware('menu:indikator_apm');
        Route::get('/aps', [IndikatorKinerjaController::class, 'aps'])->name('aps')->middleware('menu:indikator_aps');
        Route::get('/guru', [IndikatorKinerjaController::class, 'guru'])->name('guru')->middleware('menu:indikator_guru');
        Route::get('/paud', [IndikatorKinerjaController::class, 'paud'])->name('paud')->middleware('menu:indikator_paud');
    });

    // Tujuan 1: Penduduk Usia Sekolah per Kecamatan
    Route::prefix('tujuan1')->name('tujuan1.')->middleware('menu:tujuan1')->group(function () {
        Route::get('/penduduk', [TujuanSatuController::class, 'index'])->name('index');
        Route::post('/penduduk', [TujuanSatuController::class, 'store'])->name('store');
        Route::put('/penduduk/{id}', [TujuanSatuController::class, 'update'])->name('update');
    });

    // Tujuan 2: Perolehan Prestasi Siswa
    Route::prefix('tujuan2')->name('tujuan2.')->middleware('menu:tujuan2')->group(function () {
        Route::get('/prestasi', [TujuanDuaController::class, 'index'])->name('index');
        Route::post('/prestasi', [TujuanDuaController::class, 'store'])->name('store');
        Route::put('/prestasi/{id}', [TujuanDuaController::class, 'update'])->name('update');
        Route::delete('/prestasi/{id}', [TujuanDuaController::class, 'destroy'])->name('destroy');
    });

    // Satuan Pendidikan (Basic Sekolah & Akreditasi)
    Route::prefix('sekolah')->name('sekolah.')->middleware('menu:sekolah')->group(function () {
        Route::get('/', [SatuanPendidikanController::class, 'index'])->name('index');
        Route::post('/', [SatuanPendidikanController::class, 'store'])->name('store');
        Route::put('/{id}', [SatuanPendidikanController::class, 'update'])->name('update');
        Route::delete('/{id}', [SatuanPendidikanController::class, 'destroy'])->name('destroy');
        Route::post('/import', [SatuanPendidikanController::class, 'import'])->name('import');
    });

    // Partisipasi Siswa (Dapodik)
    Route::prefix('siswa')->name('siswa.')->middleware('menu:siswa')->group(function () {
        Route::get('/', [PartisipasiSiswaController::class, 'index'])->name('index');
        Route::post('/', [PartisipasiSiswaController::class, 'store'])->name('store');
        Route::put('/{id}', [PartisipasiSiswaController::class, 'update'])->name('update');
        Route::delete('/{id}', [PartisipasiSiswaController::class, 'destroy'])->name('destroy');
        Route::post('/import', [PartisipasiSiswaController::class, 'import'])->name('import');
    });

    // Guru Pendidik & Tenaga Kependidikan
    Route::prefix('guru-pendidik')->name('guru.')->middleware('menu:guru')->group(function () {
        Route::get('/', [GuruPendidikController::class, 'index'])->name('index');
        Route::post('/', [GuruPendidikController::class, 'store'])->name('store');
        Route::put('/{id}', [GuruPendidikController::class, 'update'])->name('update');
        Route::delete('/{id}', [GuruPendidikController::class, 'destroy'])->name('destroy');
        Route::post('/import', [GuruPendidikController::class, 'import'])->name('import');
    });

    // Target Renstra 2025-2029 & Realisasi
    Route::prefix('renstra')->name('renstra.')->group(function () {
        Route::get('/target', [RenstraAkipController::class, 'target'])->name('target')->middleware('menu:renstra_target');
        Route::post('/target', [RenstraAkipController::class, 'storeTarget'])->name('target.store')->middleware('menu:renstra_target');
        Route::put('/target/{id}', [RenstraAkipController::class, 'updateTarget'])->name('target.update')->middleware('menu:renstra_target');
        Route::delete('/target/{id}', [RenstraAkipController::class, 'destroyTarget'])->name('target.destroy')->middleware('menu:renstra_target');
        Route::get('/realisasi', [RenstraAkipController::class, 'realisasi'])->name('realisasi')->middleware('menu:renstra_realisasi');
        Route::post('/realisasi', [RenstraAkipController::class, 'storeRealisasi'])->name('realisasi.store')->middleware('menu:renstra_realisasi');
        Route::put('/realisasi/{id}', [RenstraAkipController::class, 'updateRealisasi'])->name('realisasi.update')->middleware('menu:renstra_realisasi');
        Route::delete('/realisasi/{id}', [RenstraAkipController::class, 'destroyRealisasi'])->name('realisasi.destroy')->middleware('menu:renstra_realisasi');
    });

    // Peta Sebaran GIS
    Route::get('/peta-sebaran', [PetaSebaranController::class, 'index'])->name('peta.index')->middleware('menu:peta');

    // Evaluasi (Dialihkan ke Realisasi Capaian Kinerja)
    Route::get('/evaluasi', fn () => redirect()->route('renstra.realisasi'))->name('evaluasi.index');

    // Laporan Kinerja LKjIP Resmi
    Route::get('/laporan/lkjip', [LaporanKinerjaController::class, 'lkjip'])->name('laporan.lkjip')->middleware('menu:laporan_lkjip');

    // Audit Trail / Riwayat Perubahan Data
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit.index')->middleware('menu:audit');

    // Modul Glosarium & SOP Panduan SAKIP
    Route::view('/panduan-sakip', 'panduan.index')->name('panduan.index')->middleware('menu:panduan');

    // Export Excel (.xlsx)
    Route::prefix('export')->name('export.')->middleware('menu:export')->group(function () {
        Route::get('/kecamatan', [ExportController::class, 'unduhKecamatanCsv'])->name('kecamatan');
        Route::get('/penduduk', [ExportController::class, 'unduhPendudukCsv'])->name('penduduk');
        Route::get('/prestasi', [ExportController::class, 'unduhPrestasiCsv'])->name('prestasi');
        Route::get('/sekolah', [ExportController::class, 'unduhSekolahCsv'])->name('sekolah');
        Route::get('/siswa', [ExportController::class, 'unduhSiswaCsv'])->name('siswa');
        Route::get('/guru', [ExportController::class, 'unduhGuruCsv'])->name('guru');
    });

    // Import Data Excel / CSV
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/penduduk', [ImportController::class, 'importPenduduk'])->name('penduduk');
        Route::post('/prestasi', [ImportController::class, 'importPrestasi'])->name('prestasi');
        Route::get('/template/{type}', [ImportController::class, 'downloadTemplate'])->name('template');
    });

    // Profil & Pengaturan Aplikasi
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update')->middleware('menu:pengaturan');

    // Manajemen Pengguna & Matriks Hak Akses Menu
    Route::prefix('manajemen-pengguna')->name('users.')->middleware('menu:manajemen_pengguna')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/permissions', [UserController::class, 'updatePermissions'])->name('permissions.update');
    });
});
