<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');
Route::redirect('/login', '/admin/login')->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/search', SearchController::class)->name('search');
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');

        Route::resource('students', StudentController::class)->except('show');
        Route::resource('teachers', TeacherController::class)->except('show');

        Route::prefix('import/{type}')->whereIn('type', array_keys(ImportController::IMPORTS))->group(function () {
            Route::get('/', [ImportController::class, 'create'])->name('import.create');
            Route::post('/', [ImportController::class, 'store'])->name('import.store');
            Route::get('/template', [ImportController::class, 'template'])->name('import.template');
        });
    });
});
