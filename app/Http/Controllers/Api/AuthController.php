<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolParent;

class AuthController extends Controller
{
    /**
     * 1. Login Guru via NUPTK & Password
     */
    public function loginTeacher(Request $request)
    {
        $validated = $request->validate([
            'nuptk' => 'required|string',
            'password' => 'required|string',
        ]);

        $teacher = Teacher::with('user')->where('nuptk', $validated['nuptk'])->first();

        if (!$teacher || !$teacher->user) {
            return response()->json([
                'status' => 'error',
                'message' => 'NUPTK tidak ditemukan dalam sistem.'
            ], 404);
        }

        if (!$teacher->user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun tenaga pendidik ini dinonaktifkan.'
            ], 403);
        }

        if (!Hash::check($validated['password'], $teacher->user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata sandi tidak sesuai.'
            ], 401);
        }

        // Buat Sanctum token
        $token = $teacher->user->createToken('teacher-auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Autentikasi Guru berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $teacher->user->id,
                    'email' => $teacher->user->email,
                    'role' => $teacher->user->role,
                ],
                'teacher' => [
                    'id' => $teacher->id,
                    'nuptk' => $teacher->nuptk,
                    'full_name' => $teacher->full_name,
                    'phone_number' => $teacher->phone_number,
                ]
            ]
        ], 200);
    }

    /**
     * 2. Login Orang Tua via NIS Murid & Password
     */
    public function loginParent(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string',
            'password' => 'required|string',
        ]);

        $student = Student::with('schoolClass')->where('nis', $validated['nis'])->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'NIS siswa tidak terdaftar.'
            ], 404);
        }

        // Cari akun wali murid berdasarkan username (NIS)
        $user = User::where('username', $validated['nis'])
            ->where('role', 'parent')
            ->first();

        // Fallback: Jika username bukan NIS, cari via relasi parent_students
        if (!$user) {
            $parentRecord = $student->parents()->with('user')->first();
            $user = $parentRecord?->user;
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun orang tua untuk siswa ini belum didaftarkan.'
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun orang tua dinonaktifkan oleh administrator.'
            ], 403);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata sandi tidak sesuai.'
            ], 401);
        }

        $parent = SchoolParent::where('user_id', $user->id)->first();
        $token = $user->createToken('parent-auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Autentikasi Orang Tua berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'parent' => [
                    'id' => $parent?->id,
                    'full_name' => $parent?->full_name ?? 'Wali Murid',
                    'phone_number' => $parent?->phone_number,
                ],
                'student' => [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'full_name' => $student->full_name,
                    'class' => $student->schoolClass->name ?? '-',
                    'birth_date' => $student->birth_date,
                    'is_active' => $student->is_active,
                ]
            ]
        ], 200);
    }

    /**
     * 3. Ambil Profil Pengguna Aktif (Protected)
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $profile = null;

        if ($user->role === 'teacher') {
            $profile = Teacher::where('user_id', $user->id)->first();
        } elseif ($user->role === 'parent') {
            $profile = SchoolParent::with('students.schoolClass')->where('user_id', $user->id)->first();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
                'profile' => $profile,
            ]
        ], 200);
    }

    /**
     * 4. Logout Sesi (Revoke Token Aktif)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi autentikasi telah diakhiri.'
        ], 200);
    }

    /**
     * 5. Lupa Kata Sandi - Kirim Kode OTP 6-Digit ke Gmail
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat email tidak terdaftar dalam sistem sekolah.'
            ], 404);
        }

        // Generate 6 digit angka acak (100000 - 999999)
        $otp = (string) random_int(100000, 999999);

        // Simpan / update di tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Masukkan email ke antrean agar respons API tidak menunggu SMTP Gmail.
        // Pengiriman dijalankan oleh worker (`composer run dev` / `php artisan queue:work`).
        $emailQueued = false;
        try {
            Mail::to($user->email)->queue(new OtpMail($otp, $user));
            $emailQueued = true;
        } catch (\Throwable $e) {
            Log::error('Gagal mengantrekan email OTP: ' . $e->getMessage());
        }

        $responseData = [
            'email' => $user->email,
            'expires_in_minutes' => 10,
            'email_sent' => $emailQueued,
        ];

        // Sertakan debug_otp jika di environment lokal untuk kemudahan pengujian
        if (app()->environment('local')) {
            $responseData['debug_otp'] = $otp;
        }

        return response()->json([
            'status' => 'success',
            'message' => $emailQueued
                ? 'Kode verifikasi 6 digit sedang dikirim ke Gmail Anda.'
                : 'Kode verifikasi telah dibuat. Periksa inbox/spam atau log sistem.',
            'data' => $responseData,
        ], 200);
    }

    /**
     * 6. Verifikasi Kode OTP 6-Digit
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permintaan reset kata sandi tidak ditemukan atau sudah kadaluwarsa.'
            ], 400);
        }

        // Cek kedaluwarsa 10 menit
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP telah kadaluwarsa. Silakan minta kode verifikasi baru.'
            ], 400);
        }

        // Cek kecocokan OTP
        if (!Hash::check($validated['otp'], $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP yang dimasukkan tidak valid.'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP valid. Silakan tentukan kata sandi baru.',
            'data' => [
                'email' => $validated['email']
            ]
        ], 200);
    }

    /**
     * 7. Reset Kata Sandi Baru
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permintaan reset kata sandi tidak ditemukan.'
            ], 400);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP telah kadaluwarsa.'
            ], 400);
        }

        if (!Hash::check($validated['otp'], $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak valid.'
            ], 400);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Hapus token reset yang sudah digunakan
        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        // Revoke seluruh sesi token lama
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diperbarui. Silakan login kembali dengan kata sandi baru Anda.'
        ], 200);
    }
}
