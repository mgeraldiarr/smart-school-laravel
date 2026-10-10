<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\SchoolParent;
use App\Models\Attendance;
use App\Models\Schedule;

class AdminDashboardController extends Controller
{
    // 1. Halaman Login Admin
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // 2. Proses Login Admin
    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            Auth::logout();
            return back()->withErrors(['email' => 'Hanya akun Admin yang berhak masuk ke portal ini.']);
        }

        return back()->withErrors(['email' => 'Email atau kata sandi admin salah.']);
    }

    // 3. Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // 4. Halaman Dashboard Utama
    public function index()
    {
        $totalSiswa = Student::count();
        $totalGuru = Teacher::count();
        $totalKelas = SchoolClass::count();
        $totalHadirHariIni = Attendance::where('date', now()->toDateString())
            ->where('status', 'Hadir')
            ->count();

        $attendancesRecent = Attendance::with(['student.schoolClass', 'schedule.subject'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalHadirHariIni',
            'attendancesRecent'
        ));
    }

    // 5. Halaman Data Siswa
    public function students()
    {
        $students = Student::with('schoolClass')->latest()->paginate(15);
        return view('admin.students', compact('students'));
    }

    // 6. Halaman Data Guru
    public function teachers()
    {
        $teachers = Teacher::with('user')->latest()->paginate(15);
        return view('admin.teachers', compact('teachers'));
    }

    // 7. Halaman Import Data Excel
    public function showImport()
    {
        return view('admin.import');
    }

    // 8. Proses Import Data CSV / Excel
    public function processImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file_excel');
        $handle = fopen($file->getRealPath(), 'r');

        // Lewati baris header pertama
        $header = fgetcsv($handle, 1000, ',');
        $importedCount = 0;

        // Baca baris data: [NIS, Nama Siswa, Kelas, Tgl Lahir, Nama Wali, Gmail Wali]
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) >= 6) {
                $nis = trim($row[0]);
                $namaSiswa = trim($row[1]);
                $namaKelas = trim($row[2]);
                $tglLahir = trim($row[3]); // format: YYYY-MM-DD
                $namaWali = trim($row[4]);
                $gmailWali = trim($row[5]);

                // Cari atau buat Kelas
                $class = SchoolClass::firstOrCreate(
                    ['name' => $namaKelas],
                    ['grade' => '8', 'academic_year' => '2026/2027']
                );

                // Buat Siswa
                $student = Student::updateOrCreate(
                    ['nis' => $nis],
                    [
                        'full_name' => $namaSiswa,
                        'birth_date' => $tglLahir,
                        'class_id' => $class->id,
                        'is_active' => true,
                    ]
                );

                // Buat Akun Orang Tua
                $userOrtu = User::updateOrCreate(
                    ['email' => $gmailWali],
                    [
                        'username' => $nis,
                        'password' => Hash::make('password123'),
                        'role' => 'parent',
                        'is_active' => true,
                    ]
                );

                $parent = SchoolParent::updateOrCreate(
                    ['user_id' => $userOrtu->id],
                    ['full_name' => $namaWali]
                );

                // Hubungkan Orang Tua ke Siswa jika belum terhubung
                if (!$parent->students()->where('student_id', $student->id)->exists()) {
                    $parent->students()->attach($student->id);
                }

                $importedCount++;
            }
        }
        fclose($handle);

        return back()->with('success', "Sukses mengimpor {$importedCount} data siswa & wali murid ke database!");
    }
}
