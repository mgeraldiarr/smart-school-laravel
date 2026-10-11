<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Grade;
use Carbon\Carbon;

class ParentMonitoringController extends Controller
{
    /**
     * Helper untuk mengambil data anak dari akun Orang Tua yang sedang login
     */
    private function getStudentForParent(Request $request)
    {
        $user = Auth::user();
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            return null;
        }

        // Jika orang tua memilih anak tertentu (opsional), atau default anak pertama
        if ($request->has('student_id')) {
            return $parent->students()->with('schoolClass')->find($request->student_id);
        }

        return $parent->students()->with('schoolClass')->first();
    }

    /**
     * 1. Monitoring Status Kehadiran Anak Hari Ini (Real-Time)
     * GET /api/parent/student-status
     */
    public function todayStatus(Request $request)
    {
        $student = $this->getStudentForParent($request);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data anak tidak ditemukan untuk akun orang tua ini.'
            ], 404);
        }

        $date = $request->query('date', Carbon::today()->toDateString());

        // Pemetaan nama hari bahasa Indonesia
        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $dayOfWeek = $daysMap[Carbon::parse($date)->dayOfWeek];

        // Ambil jadwal pelajaran anak pada hari tersebut
        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get();

        // Ambil riwayat presensi anak pada tanggal tersebut
        $attendances = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->get()
            ->keyBy('schedule_id');

        $scheduleStatuses = $schedules->map(function ($sch) use ($attendances) {
            $record = $attendances->get($sch->id);
            return [
                'schedule_id'   => $sch->id,
                'subject'       => $sch->subject->name ?? '-',
                'teacher'       => $sch->teacher->full_name ?? '-',
                'time_slot'     => "{$sch->start_time} - {$sch->end_time}",
                'status'        => $record ? $record->status : 'Belum_Ada_Kabar',
                'method'        => $record ? $record->method : null,
                'check_in_time' => $record ? Carbon::parse($record->check_in_time)->format('H:i') : null,
            ];
        });

        // Kesimpulan status keseluruhan hari ini
        $overallStatus = 'Belum Hadir';
        if ($attendances->contains('status', 'Hadir')) {
            $overallStatus = 'Hadir di Sekolah';
        } elseif ($attendances->contains('status', 'Sakit')) {
            $overallStatus = 'Sakit';
        } elseif ($attendances->contains('status', 'Izin')) {
            $overallStatus = 'Izin';
        } elseif ($attendances->contains('status', 'Alpa')) {
            $overallStatus = 'Tidak Hadir (Alpa)';
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student' => [
                    'id'         => $student->id,
                    'nis'        => $student->nis,
                    'name'       => $student->full_name,
                    'class'      => $student->schoolClass->name ?? '-',
                ],
                'date_info' => [
                    'date'        => $date,
                    'day'         => $dayOfWeek,
                    'live_status' => $overallStatus,
                ],
                'schedules_today' => $scheduleStatuses,
            ]
        ], 200);
    }

    /**
     * 2. Rekap Kartu Nilai Akademik Anak (Tugas, UTS, UAS)
     * GET /api/parent/student-grades
     */
    public function studentGrades(Request $request)
    {
        $student = $this->getStudentForParent($request);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data anak tidak ditemukan untuk akun orang tua ini.'
            ], 404);
        }

        $gradesQuery = Grade::with(['subject', 'teacher'])
            ->where('student_id', $student->id)
            ->latest('assessment_date');

        if ($request->has('subject_id')) {
            $gradesQuery->where('subject_id', $request->subject_id);
        }

        if ($request->has('assessment_type')) {
            $gradesQuery->where('assessment_type', $request->assessment_type);
        }

        $grades = $gradesQuery->get();

        // Hitung rata-rata nilai per mata pelajaran
        $gradesBySubject = $grades->groupBy(fn($g) => $g->subject->name ?? 'Lainnya')->map(function ($items, $subjectName) {
            return [
                'subject_name'  => $subjectName,
                'average_score' => round($items->avg('score'), 1),
                'assessments'   => $items->map(function ($item) {
                    return [
                        'id'              => $item->id,
                        'type'            => $item->assessment_type,
                        'title'           => $item->title,
                        'score'           => (float) $item->score,
                        'teacher_notes'   => $item->teacher_notes,
                        'assessment_date' => $item->assessment_date,
                        'teacher_name'    => $item->teacher->full_name ?? '-',
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student' => [
                    'id'    => $student->id,
                    'nis'   => $student->nis,
                    'name'  => $student->full_name,
                    'class' => $student->schoolClass->name ?? '-',
                ],
                'summary' => [
                    'total_assessments' => $grades->count(),
                    'overall_average'   => round($grades->avg('score') ?? 0, 1),
                ],
                'grades_by_subject' => $gradesBySubject,
            ]
        ], 200);
    }
}
