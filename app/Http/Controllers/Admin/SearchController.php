<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $students = collect();
        $teachers = collect();

        if (mb_strlen($search) >= 2) {
            $students = Student::with('schoolClass')
                ->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"))
                ->orderBy('full_name')
                ->limit(15)
                ->get();

            $teachers = Teacher::with('user')
                ->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%")))
                ->orderBy('full_name')
                ->limit(15)
                ->get();
        }

        return view('admin.search', compact('search', 'students', 'teachers'));
    }
}
