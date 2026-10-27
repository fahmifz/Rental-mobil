<?php

use App\Http\Controllers\AdminReservasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriMobilController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MobilController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\ReservasiController;
use Illuminate\Support\Facades\Route;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/', [PublicLandingController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/signin', [AuthController::class, 'login'])->name('login.store');
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/signup', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:pelanggan'])->group(function (): void {
    Route::get('/akun', [AuthController::class, 'account'])->name('account');

    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
    Route::get('/reservasi/buat', [ReservasiController::class, 'create'])->name('reservasi.create');
    Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
    Route::get('/reservasi/{reservasi}', [ReservasiController::class, 'show'])->name('reservasi.show');
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'Rental Mobil Dashboard']);
    })->name('dashboard');

    Route::get('/admin/reservasi', [AdminReservasiController::class, 'index'])
        ->name('admin.reservasi.index');
    Route::post('/admin/reservasi/{reservasi}/setujui', [AdminReservasiController::class, 'approve'])
        ->name('admin.reservasi.approve');
    Route::post('/admin/reservasi/{reservasi}/tolak', [AdminReservasiController::class, 'reject'])
        ->name('admin.reservasi.reject');

    Route::resource('kategori-mobil', KategoriMobilController::class)->except('show');
    Route::resource('mobil', MobilController::class)->except('show');
});

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');
