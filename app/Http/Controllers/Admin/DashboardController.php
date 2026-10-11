<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardStats $stats): View
    {
        $focusDate = $stats->focusDate();
        $focus = $stats->statusOn($focusDate);
        $activeStudents = $stats->activeStudents();

        return view('admin.dashboard', [
            'periodDays' => $stats->periodDays(),
            'activeStudents' => $activeStudents,
            'teacherCount' => $stats->teacherCount(),
            'classCount' => $stats->classCount(),
            'focusDate' => $focusDate,
            'focus' => $focus,
            'presentRate' => $activeStudents > 0 ? round($focus['Hadir'] / $activeStudents * 100) : null,
            'presentBefore' => $stats->statusOn($stats->previousSchoolDay($focusDate))['Hadir'],
            'trend' => $stats->dailyTrend(),
            'byClass' => $stats->attendanceByClass(),
            'grades' => $stats->gradeAverages(),
            'averageUts' => $stats->averageScore('UTS'),
            'averageUas' => $stats->averageScore('UAS'),
            'compliance' => $stats->teacherCompliance(),
            'recent' => $stats->recentAttendances(),
        ]);
    }
}
