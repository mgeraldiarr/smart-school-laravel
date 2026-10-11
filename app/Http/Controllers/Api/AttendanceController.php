<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /** 1. Presensi wajah (face scan), Dipanggil dari kamera HP Guru saat wajah siswa terdeteksi **/

    public function faceScan(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'schedule_id' => 'required|exists:schedules,id',
            'confidence_score' => 'nullable|numeric',
            'capture_photo' => 'nullable|string',
        ]);

        $today = Carbon::now()->toDateString();
        $student = Student::with(['schoolClass'])->findOrFail($validated['student_id']);
        $schedule = Schedule::with(['subject', 'schoolClass'])->findOrFail($validated['schedule_id']);

        // Proteksi anti double-scan: cek apakah sistem sudah absen di jadwal hari ini untuk siswa yang sama
        $existing = Attendance::where('student_id', $student->id)
            ->where('schedule_id', $schedule->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => "Siswa {$student->full_name} sudah tercatat presensi sebelumnya pada pukul " . Carbon::parse($existing->check_in_time)->format('H:i') . 'WIB',
                'data' => [
                    'student-name' => $student->full_name,
                    'status' => $existing->status,
                    'check-in-time' => $existing->check_in_time,
                ]
            ], 409); // Conflict
        }

        // simpan presensi wajah (otomatis hadir)
        $attendance = Attendance::create([
            'student_id'          => $student->id,
            'schedule_id'         => $schedule->id,
            'date'                => $today,
            'check_in_time'       => Carbon::now()->toTimeString(),
            'status'              => 'Hadir',
            'method'              => 'face_scan',
            'input_by_teacher_id' => Auth::user()->teacher->id ?? null,
            'confidence_score'    => $validated['confidence_score'] ?? 0.95,
            'captured_photo_url'  => $validated['captured_photo'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Presensi wajah berhasil. {$student->full_name} telah tercatat hadir.",
            'data' => [
                'attendance_id' => $attendance->id,
                'student_nis' => $student->nis,
                'student_name' => $student->full_name,
                'class' => $student->schoolClass->name ?? '-',
                'subject' => $schedule->subject->name ?? '-',
                'status' => $attendance->status,
                'method' => $attendance->method,
                'check_in_time' => Carbon::parse($attendance->check_in_time)->format('H:i:s'),
            ]
        ], 201);
    }

    // 2.  Tampilan sesi presensi kelas terkini (guru), 
    // mengambil daftar seluruh siswa di kelas beserta status kehadirannya hari ini
    public function sessionAttendance(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'nullable|date',
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $schedule = Schedule::with(['subject', 'schoolClass', 'teacher'])->findOrFail($validated['schedule_id']);

        // ambil seluruh siswa di kelas tersebut
        $students = Student::where('class_id', $schedule->class_id)
            ->where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

        // ambil presensi yang sudah tercatat hari ini pada jadwal ini
        $attendances = Attendance::where('schedule_id', $schedule->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        // gabungkan data siswa dengan status presensi
        $sessionList = $students->map(function ($s) use ($attendances) {
            $record = $attendances->get($s->id);
            return [
                'student_id' => $s->id,
                'nis' => $s->nis,
                'name' => $s->full_name,
                'status' => $record ? $record->status : 'Belum_Absen',
                'method' => $record ? $record->method : null,
                'check_in_time' => $record ? Carbon::parse($record->check_in_time)->format('H:i') : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'schedule' => [
                    'id'          => $schedule->id,
                    'class_name'  => $schedule->schoolClass->name ?? '-',
                    'subject'     => $schedule->subject->name ?? '-',
                    'day'         => $schedule->day_of_week,
                    'time'        => "{$schedule->start_time} - {$schedule->end_time}",
                    'date'        => $date,
                ],
                'summary' => [
                    'total_students' => $students->count(),
                    'hadir'          => $sessionList->where('status', 'Hadir')->count(),
                    'sakit'          => $sessionList->where('status', 'Sakit')->count(),
                    'izin'           => $sessionList->where('status', 'Izin')->count(),
                    'alpa'           => $sessionList->where('status', 'Alpa')->count(),
                    'belum_absen'    => $sessionList->where('status', 'Belum_Absen')->count(),
                ],
                'students' => $sessionList->values(),
            ]
        ], 200);
    }

    // 3. Fallback Manual Guru
    // Guru mengubah status siswa menjadi hadir, sakit, izin, atau alpa secara manual
    public function manualAttendance(Request $request)
    {
        $validated = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'schedule_id' => 'required|exists:schedules,id',
            'status'      => 'required|in:Hadir,Sakit,Izin,Alpa',
            'date'        => 'nullable|date',
        ]);
        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $student = Student::findOrFail($validated['student_id']);
        $schedule = Schedule::findOrFail($validated['schedule_id']);
        $teacherId = Auth::user()->teacher->id ?? null;
        // Update jika sudah ada, atau buat baru jika belum absen
        $attendance = Attendance::updateOrCreate(
            [
                'student_id'  => $student->id,
                'schedule_id' => $schedule->id,
                'date'        => $date,
            ],
            [
                'status'              => $validated['status'],
                'method'              => 'manual_teacher',
                'check_in_time'       => Carbon::now()->toTimeString(),
                'input_by_teacher_id' => $teacherId,
            ]
        );
        return response()->json([
            'status'  => 'success',
            'message' => "Presensi manual berhasil diperbarui untuk {$student->full_name} menjadi {$validated['status']}.",
            'data'    => [
                'student_id'    => $student->id,
                'student_name'  => $student->full_name,
                'status'        => $attendance->status,
                'method'        => $attendance->method,
                'check_in_time' => Carbon::parse($attendance->check_in_time)->format('H:i:s'),
            ]
        ], 200);    
    }

    // 4. ambil jadwal mengajar guru 
    // GET /api/teacher/schedules
    public function teacherSchedules(Request $request)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data profil guru tidak ditemukan.'
            ], 404);
        }
        $day = $request->query('day'); // filter opsional: misal "Senin"
        $query = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id);
        if ($day) {
            $query->where('day_of_week', $day);
        }
        $schedules = $query->orderBy('start_time', 'asc')->get()->map(function ($sch) {
            return [
                'id'           => $sch->id,
                'class_id'     => $sch->class_id,
                'class_name'   => $sch->schoolClass->name ?? '-',
                'subject_id'   => $sch->subject_id,
                'subject_name' => $sch->subject->name ?? '-',
                'day_of_week'  => $sch->day_of_week,
                'start_time'   => Carbon::parse($sch->start_time)->format('H:i'),
                'end_time'     => Carbon::parse($sch->end_time)->format('H:i'),
            ];
        });
        return response()->json([
            'status' => 'success',
            'data'   => $schedules,
        ], 200);
    }

    // 5. ambil daftar siswa berdasarkan kelas (untuk form nilai dan presensi)
    public function classStudents(int $classId)
    {
        $students = Student::where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get(['id', 'nis', 'full_name', 'class_id']);
        return response()->json([
            'status' => 'success',
            'total'  => $students->count(),
            'data'   => $students,
        ], 200);
    }
}
