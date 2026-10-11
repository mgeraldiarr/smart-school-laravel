<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParentMonitoringController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AttendanceController;


/*
|--------------------------------------------------------------------------
| API Routes - Smart School
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/teacher/login', [AuthController::class, 'loginTeacher']);
    Route::post('/parent/login', [AuthController::class, 'loginParent']);
    
    // Pemulihan Akun via Gmail OTP
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Endpoints (Memerlukan Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // autentikasi dan sesi
     Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // modul presensi wajah dan guru
    Route::post('/attendance/face-scan', [AttendanceController::class, 'faceScan']);
    Route::get('/teacher/attendance/session', [AttendanceController::class, 'sessionAttendance']);
    Route::post('/teacher/attendance/manual', [AttendanceController::class, 'manualAttendance']);

    // modul nilai akademik siswa (guru)
    Route::get('/teacher/grades', [GradeController::class, 'index']);
    Route::post('/teacher/grades', [GradeController::class, 'store']);
    Route::put('/teacher/grades/{id}', [GradeController::class, 'update'])->whereNumber('id');
    Route::delete('/teacher/grades/{id}', [GradeController::class, 'destroy'])->whereNumber('id');

    // modul monitoring orang tua
    Route::prefix('parent')->group(function () {
        Route::get('/student-status', [ParentMonitoringController::class, 'todayStatus']);
        Route::get('/student-grades', [ParentMonitoringController::class, 'studentGrades']);
    });

    // penunjang mobile: jadwal guru dan daftar siswa per kelas
    Route::get('/teacher/schedules', [AttendanceController::class, 'teacherSchedules']);
    Route::get('/teacher/classes/{classId}/students', [AttendanceController::class, 'classStudents'])->whereNumber('classId');
});
