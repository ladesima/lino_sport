<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TvDisplayController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\Admin\RateController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CashierAccountController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. PUBLIC ROUTES (Dapat Diakses Bebas Tanpa Login / Tanpa Daftar Akun)
// =========================================================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

// Alur Booking Lapangan (Pembayaran QRIS Statis, Upload Bukti QRIS & Approval Admin)
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store')->middleware('throttle:10,1');
Route::get('/booking/{booking}/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
Route::post('/booking/{booking}/upload-proof', [BookingController::class, 'uploadProof'])->name('booking.upload-proof')->middleware('throttle:15,1');
Route::get('/booking/{booking}/receipt', [BookingController::class, 'receipt'])->name('booking.receipt');
Route::get('/booking/check', [BookingController::class, 'check'])->name('booking.check')->middleware('throttle:20,1');

// TV Display Live Schedule (Untuk Layar Monitor/TV Sport Center)
Route::get('/tv-display', [TvDisplayController::class, 'index'])->name('tv.display');
Route::get('/api/tv-status', [TvDisplayController::class, 'apiStatus'])->name('api.tv-status');

// API Endpoint Jadwal Jam
Route::get('/api/court-schedule', [BookingController::class, 'courtSchedule'])->name('api.court-schedule');

// =========================================================================
// 2. AUTHENTICATION (Khusus Admin Utama & User Admin)
// =========================================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =========================================================================
// 3. ADMIN PANEL
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // A. Menu Operasional (Dapat diakses oleh Admin Utama & seluruh User Admin):
    //    1. Dashboard
    //    2. Jadwal Lapangan
    //    3. Daftar Booking & Approval
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('bookings', [BookingAdminController::class, 'index'])->name('bookings.index');
    Route::get('bookings/schedule', [BookingAdminController::class, 'schedule'])->name('bookings.schedule');
    Route::get('bookings/export', [BookingAdminController::class, 'export'])->name('bookings.export');
    Route::get('api/pending-alerts', [BookingAdminController::class, 'pendingAlerts'])->name('api.pending-alerts');
    Route::get('bookings/{booking}', [BookingAdminController::class, 'show'])->name('bookings.show');
    Route::get('bookings/{booking}/receipt', [BookingAdminController::class, 'receipt'])->name('bookings.receipt');
    Route::patch('bookings/{booking}/confirm', [BookingAdminController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('bookings/{booking}/cancel', [BookingAdminController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/cash-settlement', [BookingAdminController::class, 'cashSettlement'])->name('bookings.cash-settlement');

    // B. Menu Khusus Admin Utama (Super Admin Only):
    //    Laporan Keuangan, Master Data (Lapangan, Tarif, Banner), & Kelola User Admin
    Route::middleware('super_admin')->group(function () {
        Route::resource('courts', CourtController::class);
        Route::resource('rates', RateController::class);
        Route::resource('sliders', SliderController::class);
        Route::resource('cashiers', CashierAccountController::class);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
