<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

// Pengalihan halaman awal langsung ke portal Admin
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Route Otentikasi Admin
Route::get('/admin/login', [AdminDashboardController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminDashboardController::class, 'processLogin'])->name('admin.login.process');
Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])->name('admin.logout');

// Route Dashboard Admin (Wajib Login sebagai Admin)
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/students', [AdminDashboardController::class, 'students'])->name('admin.students');
    Route::get('/teachers', [AdminDashboardController::class, 'teachers'])->name('admin.teachers');
    Route::get('/import', [AdminDashboardController::class, 'showImport'])->name('admin.import');
    Route::post('/import', [AdminDashboardController::class, 'processImport'])->name('admin.import.process');
});
