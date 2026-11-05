
<?php

use Illuminate\Support\Facades\Route;

// ==========================================================
// CONTROLLER APLIKASI RENTAL MOBIL
// ==========================================================

// Public & Authentication
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\PublicLandingController;

// Pelanggan
use App\Http\Controllers\Pelanggan\ReservasiController;
use App\Http\Controllers\Pelanggan\RekomendasiController;

// Admin
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminReservasiController;
use App\Http\Controllers\Admin\KategoriMobilController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MobilController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PengembalianController;
use App\Http\Controllers\Admin\RefundController;


/*
|--------------------------------------------------------------------------
| 1. ROUTE PUBLIK & AUTENTIKASI
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', [PublicLandingController::class, 'index'])
    ->name('home');

// Pengaturan Bahasa
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// Hanya untuk pengunjung yang belum login
Route::middleware('guest')->group(function (): void {

    // Login
    Route::get('/signin', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/signin', [AuthController::class, 'login'])
        ->name('login.store');

    // Registrasi
    Route::get('/signup', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/signup', [AuthController::class, 'register'])
        ->name('register.store');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| 2. ROUTE PELANGGAN
|--------------------------------------------------------------------------
| Hanya dapat diakses oleh pengguna dengan role pelanggan.
*/

Route::middleware(['auth', 'role:pelanggan'])->group(function (): void {

    // Dashboard Pelanggan
    Route::get('/akun', [AuthController::class, 'account'])
        ->name('account');

    // Rekomendasi Mobil - User-Based Collaborative Filtering
    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])
        ->name('pelanggan.rekomendasi.index');

    // Daftar Reservasi
    Route::get('/reservasi', [ReservasiController::class, 'index'])
        ->name('reservasi.index');

    // Form Reservasi
    Route::get('/reservasi/buat', [ReservasiController::class, 'create'])
        ->name('reservasi.create');

    // Simpan Reservasi
    Route::post('/reservasi', [ReservasiController::class, 'store'])
        ->name('reservasi.store');

    // Batalkan Reservasi
    Route::post('/reservasi/{reservasi}/batal', [ReservasiController::class, 'cancel'])
        ->name('reservasi.cancel');

    // Berikan Rating Mobil
    Route::post('/reservasi/{reservasi}/rating', [ReservasiController::class, 'storeRating'])
        ->name('reservasi.rating.store');

    // Detail Reservasi
    Route::get('/reservasi/{reservasi}', [ReservasiController::class, 'show'])
        ->name('reservasi.show');
});


/*
|--------------------------------------------------------------------------
| 3. ROUTE ADMIN - APLIKASI RENTAL MOBIL
|--------------------------------------------------------------------------
| Mengelola seluruh operasional aplikasi rental mobil.
*/

Route::middleware(['auth', 'role:admin'])->group(function (): void {

    // ======================================================
    // DASHBOARD ADMIN
    // ======================================================

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    // ======================================================
    // MASTER DATA
    // ======================================================

    // Kategori Mobil
    Route::resource('kategori-mobil', KategoriMobilController::class)
        ->except('show');

    // Data Mobil
    Route::resource('mobil', MobilController::class)
        ->except('show');

    // Data Pelanggan
    Route::get('/admin/pelanggan', [PelangganController::class, 'index'])
        ->name('admin.pelanggan.index');

    Route::get('/admin/pelanggan/{pelanggan}', [PelangganController::class, 'show'])
        ->name('admin.pelanggan.show');


    // ======================================================
    // RESERVASI
    // ======================================================

    Route::get('/admin/reservasi', [AdminReservasiController::class, 'index'])
        ->name('admin.reservasi.index');

    Route::post('/admin/reservasi/{reservasi}/setujui', [AdminReservasiController::class, 'approve'])
        ->name('admin.reservasi.approve');

    Route::post('/admin/reservasi/{reservasi}/tolak', [AdminReservasiController::class, 'reject'])
        ->name('admin.reservasi.reject');

    Route::post('/admin/reservasi/{reservasi}/batal', [AdminReservasiController::class, 'cancel'])
        ->name('admin.reservasi.cancel');


    // ======================================================
    // PEMBAYARAN
    // ======================================================

    Route::get('/admin/pembayaran', [PembayaranController::class, 'index'])
        ->name('admin.pembayaran.index');

    Route::get('/admin/pembayaran/reservasi/{reservasi}', [PembayaranController::class, 'show'])
        ->name('admin.pembayaran.show');

    Route::get('/admin/pembayaran/reservasi/{reservasi}/create', [PembayaranController::class, 'create'])
        ->name('admin.pembayaran.create');

    Route::post('/admin/pembayaran/reservasi/{reservasi}', [PembayaranController::class, 'store'])
        ->name('admin.pembayaran.store');

    Route::get('/admin/pembayaran/reservasi/{reservasi}/edit', [PembayaranController::class, 'edit'])
        ->name('admin.pembayaran.edit');

    Route::put('/admin/pembayaran/{pembayaran}', [PembayaranController::class, 'update'])
        ->name('admin.pembayaran.update');


    // ======================================================
    // PENGEMBALIAN MOBIL
    // ======================================================

    Route::get('/admin/pengembalian', [PengembalianController::class, 'index'])
        ->name('admin.pengembalian.index');

    Route::get('/admin/pengembalian/reservasi/{reservasi}', [PengembalianController::class, 'show'])
        ->name('admin.pengembalian.show');

    Route::get('/admin/pengembalian/reservasi/{reservasi}/create', [PengembalianController::class, 'create'])
        ->name('admin.pengembalian.create');

    Route::post('/admin/pengembalian/reservasi/{reservasi}', [PengembalianController::class, 'store'])
        ->name('admin.pengembalian.store');


    // ======================================================
    // REFUND
    // ======================================================

    Route::get('/admin/refund', [RefundController::class, 'index'])
        ->name('admin.refund.index');

    Route::get('/admin/refund/reservasi/{reservasi}/buat', [RefundController::class, 'create'])
        ->name('admin.refund.create');

    Route::get('/admin/refund/{refund}', [RefundController::class, 'show'])
        ->name('admin.refund.show');

    Route::post('/admin/refund/{refund}/konfirmasi', [RefundController::class, 'confirm'])
        ->name('admin.refund.confirm');


    // ======================================================
    // LAPORAN
    // ======================================================

    Route::get('/admin/laporan', [LaporanController::class, 'index'])
        ->name('admin.laporan.index');
});


/*
|--------------------------------------------------------------------------
| 4. ROUTE BAWAAN TEMPLATE TAILADMIN
|--------------------------------------------------------------------------
| Route demo, halaman UI, chart, dan komponen bawaan template.
| Dipisahkan dari route utama aplikasi rental mobil.
*/

// ==========================================================
// ERROR PAGE
// ==========================================================

Route::get('/error-404', function () {
    return view('pages.errors.error-404', [
        'title' => 'Error 404',
    ]);
})->name('error-404');


// ==========================================================
// TEMPLATE - KHUSUS ADMIN
// ==========================================================

Route::middleware(['auth', 'role:admin'])->group(function (): void {

    // ------------------------------------------------------
    // Calendar
    // ------------------------------------------------------

    Route::get('/calendar', function () {
        return view('pages.calender', [
            'title' => 'Calendar',
        ]);
    })->name('calendar');


    // ------------------------------------------------------
    // Profile
    // ------------------------------------------------------

    Route::get('/profile', [AuthController::class, 'profile'])
        ->name('profile');

    Route::put('/profile', [AuthController::class, 'updateProfile'])
        ->name('profile.update');


    // ------------------------------------------------------
    // Forms
    // ------------------------------------------------------

    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', [
            'title' => 'Form Elements',
        ]);
    })->name('form-elements');


    // ------------------------------------------------------
    // Tables
    // ------------------------------------------------------

    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', [
            'title' => 'Basic Tables',
        ]);
    })->name('basic-tables');


    // ------------------------------------------------------
    // Other Pages
    // ------------------------------------------------------

    Route::get('/blank', function () {
        return view('pages.blank', [
            'title' => 'Blank',
        ]);
    })->name('blank');


    // ------------------------------------------------------
    // Charts
    // ------------------------------------------------------

    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', [
            'title' => 'Line Chart',
        ]);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', [
            'title' => 'Bar Chart',
        ]);
    })->name('bar-chart');


    // ------------------------------------------------------
    // UI Elements
    // ------------------------------------------------------

    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', [
            'title' => 'Alerts',
        ]);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', [
            'title' => 'Avatars',
        ]);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', [
            'title' => 'Badges',
        ]);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', [
            'title' => 'Buttons',
        ]);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', [
            'title' => 'Images',
        ]);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', [
            'title' => 'Videos',
        ]);
    })->name('videos');
});
