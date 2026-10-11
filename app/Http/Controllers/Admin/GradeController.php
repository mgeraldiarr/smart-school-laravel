<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Services\DashboardStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    /** Kriteria ketuntasan minimal untuk menandai nilai yang perlu ditindaklanjuti. */
    public const KKM = 75;

    public function index(Request $request, DashboardStats $stats): View
    {
        $classId = $request->integer('kelas') ?: null;

        $summary = Grade::query()
            ->join('subjects', 'subjects.id', '=', 'grades.subject_id')
            ->when($classId, fn ($q) => $q->where('grades.class_id', $classId))
            ->selectRaw('subjects.name as subject, grades.assessment_type as type, COUNT(*) as total, AVG(score) as average, MIN(score) as lowest, MAX(score) as highest, SUM(CASE WHEN score < ? THEN 1 ELSE 0 END) as below_kkm', [self::KKM])
            ->groupBy('subjects.name', 'grades.assessment_type')
            ->orderBy('subjects.name')
            ->get()
            ->groupBy('subject');

        $belowKkm = Grade::with(['student.schoolClass', 'subject'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->whereIn('assessment_type', ['UTS', 'UAS'])
            ->where('score', '<', self::KKM)
            ->orderBy('score')
            ->limit(20)
            ->get();

        return view('admin.grades.index', [
            'classes' => SchoolClass::orderBy('name')->get(),
            'classId' => $classId,
            'chart' => $stats->gradeAverages($classId),
            'summary' => $summary,
            'belowKkm' => $belowKkm,
            'kkm' => self::KKM,
            'types' => DashboardStats::ASSESSMENTS,
        ]);
    }
}
