<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Angka-angka untuk beranda admin. Semua periode dihitung mundur dari hari ini.
 */
class DashboardStats
{
    public const STATUSES = ['Hadir', 'Sakit', 'Izin', 'Alpa'];
    public const ASSESSMENTS = ['Tugas', 'UTS', 'UAS'];

    /** Carbon dayOfWeekIso (1 = Senin) ke enum day_of_week di tabel schedules. */
    private const DAY_NAMES = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function __construct(private int $periodDays = 30) {}

    public function periodDays(): int
    {
        return $this->periodDays;
    }

    /**
     * Tanggal yang ditampilkan sebagai "hari ini": hari ini bila hari sekolah
     * (Senin–Sabtu); pada hari Minggu, hari terakhir yang punya presensi
     * (termasuk hari Minggu itu sendiri bila ada kegiatan).
     */
    public function focusDate(): Carbon
    {
        if (! today()->isSunday()) {
            return today();
        }

        $latest = Attendance::where('date', '<=', today()->toDateString())->max('date');

        return $latest ? Carbon::parse($latest)->startOfDay() : today();
    }

    /** Hari sekolah sebelum tanggal tertentu, untuk perbandingan. */
    public function previousSchoolDay(Carbon $date): Carbon
    {
        $day = $date->copy()->subDay();

        return $day->isSunday() ? $day->subDay() : $day;
    }

    public function activeStudents(): int
    {
        return Student::where('is_active', true)->count();
    }

    public function teacherCount(): int
    {
        return Teacher::count();
    }

    public function classCount(): int
    {
        return SchoolClass::count();
    }

    /**
     * Jumlah siswa unik per status pada satu tanggal. Satu siswa punya satu
     * presensi per jam pelajaran, jadi dihitung unik agar tidak dobel.
     *
     * @return Collection<string, int>
     */
    public function statusOn(Carbon $date): Collection
    {
        $counts = Attendance::whereDate('date', $date)
            ->whereIn('student_id', Student::select('id')->where('is_active', true))
            ->selectRaw('status, COUNT(DISTINCT student_id) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(self::STATUSES)->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)]);
    }

    /**
     * Tren harian selama periode: siswa unik per status per tanggal.
     *
     * @return array{labels: list<string>, series: array<string, list<int>>}
     */
    public function dailyTrend(): array
    {
        $rows = Attendance::where('date', '>=', $this->start()->toDateString())
            ->selectRaw('date, status, COUNT(DISTINCT student_id) as total')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $labels = [];
        $series = array_fill_keys(self::STATUSES, []);

        foreach ($this->schoolDays() as $day) {
            $labels[] = $day->locale('id')->translatedFormat('j M');
            $byStatus = ($rows[$day->toDateString()] ?? collect())->pluck('total', 'status');

            foreach (self::STATUSES as $status) {
                $series[$status][] = (int) ($byStatus[$status] ?? 0);
            }
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Persentase presensi berstatus Hadir per kelas selama periode.
     *
     * @return Collection<int, object{name: string, total: int, present: int, rate: ?float}>
     */
    public function attendanceByClass(): Collection
    {
        $stats = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->where('attendances.date', '>=', $this->start()->toDateString())
            ->selectRaw("students.class_id, COUNT(*) as total, SUM(CASE WHEN attendances.status = 'Hadir' THEN 1 ELSE 0 END) as present")
            ->groupBy('students.class_id')
            ->get()
            ->keyBy('class_id');

        return SchoolClass::orderBy('name')->get()->map(function ($class) use ($stats) {
            $total = (int) ($stats[$class->id]->total ?? 0);
            $present = (int) ($stats[$class->id]->present ?? 0);

            return (object) [
                'name' => $class->name,
                'total' => $total,
                'present' => $present,
                'rate' => $total > 0 ? round($present / $total * 100, 1) : null,
            ];
        });
    }

    /**
     * Rata-rata nilai per mata pelajaran untuk tiap jenis penilaian.
     *
     * @return array{labels: list<string>, series: array<string, list<?float>>}
     */
    public function gradeAverages(?int $classId = null): array
    {
        $rows = Grade::query()
            ->join('subjects', 'subjects.id', '=', 'grades.subject_id')
            ->when($classId, fn ($q) => $q->where('grades.class_id', $classId))
            ->selectRaw('subjects.name as subject, grades.assessment_type as type, AVG(grades.score) as average')
            ->groupBy('subjects.name', 'grades.assessment_type')
            ->orderBy('subjects.name')
            ->get();

        $labels = $rows->pluck('subject')->unique()->values()->all();
        $series = [];

        foreach (self::ASSESSMENTS as $type) {
            $series[$type] = array_map(function ($subject) use ($rows, $type) {
                $row = $rows->first(fn ($r) => $r->subject === $subject && $r->type === $type);

                return $row ? round((float) $row->average, 1) : null;
            }, $labels);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    public function averageScore(string $type): ?float
    {
        $average = Grade::where('assessment_type', $type)->avg('score');

        return $average === null ? null : round((float) $average, 1);
    }

    /**
     * Kepatuhan guru mengisi presensi: dari semua jam pelajaran yang terjadwal
     * selama periode, berapa yang sudah punya catatan presensi. Hari libur
     * belum diperhitungkan karena kalender akademik belum ada di sistem.
     *
     * @return Collection<int, object{name: string, expected: int, recorded: int, rate: ?float}>
     */
    public function teacherCompliance(): Collection
    {
        $dayCounts = collect($this->schoolDays())
            ->countBy(fn (Carbon $day) => self::DAY_NAMES[$day->dayOfWeekIso]);

        $recorded = Attendance::query()
            ->join('schedules', 'schedules.id', '=', 'attendances.schedule_id')
            ->where('attendances.date', '>=', $this->start()->toDateString())
            ->selectRaw('schedules.teacher_id, COUNT(DISTINCT '.$this->sessionKey().') as sessions')
            ->groupBy('schedules.teacher_id')
            ->pluck('sessions', 'teacher_id');

        $expected = Schedule::query()
            ->select('teacher_id', 'day_of_week', DB::raw('COUNT(*) as slots'))
            ->groupBy('teacher_id', 'day_of_week')
            ->get()
            ->groupBy('teacher_id')
            ->map(fn ($rows) => $rows->sum(fn ($r) => $r->slots * ($dayCounts[$r->day_of_week] ?? 0)));

        return Teacher::orderBy('full_name')->get()
            ->map(function ($teacher) use ($expected, $recorded) {
                $exp = (int) ($expected[$teacher->id] ?? 0);
                $rec = min((int) ($recorded[$teacher->id] ?? 0), $exp);

                return (object) [
                    'id' => $teacher->id,
                    'name' => $teacher->full_name,
                    'expected' => $exp,
                    'recorded' => $rec,
                    'rate' => $exp > 0 ? round($rec / $exp * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($t) => $t->rate ?? 101)
            ->values();
    }

    public function recentAttendances(int $limit = 8): Collection
    {
        return Attendance::with(['student.schoolClass', 'schedule.subject'])
            ->latest()
            ->take($limit)
            ->get();
    }

    private function start(): Carbon
    {
        return today()->subDays($this->periodDays - 1);
    }

    /** @return list<Carbon> hari Senin–Sabtu dalam periode, sampai hari ini */
    private function schoolDays(): array
    {
        $days = [];
        for ($day = $this->start(); $day->lte(today()); $day = $day->addDay()) {
            if ($day->dayOfWeekIso <= 6) {
                $days[] = $day->copy();
            }
        }

        return $days;
    }

    /** Satu sesi = satu jadwal pada satu tanggal; ditulis portabel untuk MySQL & SQLite. */
    private function sessionKey(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "attendances.schedule_id || '-' || attendances.date"
            : "CONCAT(attendances.schedule_id, '-', attendances.date)";
    }
}
