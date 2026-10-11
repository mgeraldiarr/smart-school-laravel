<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DataConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private StudentService $students) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $students = Student::with('schoolClass')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")))
            ->when($request->integer('kelas'), fn ($q, $classId) => $q->where('class_id', $classId))
            ->when($request->query('status') === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($request->query('status') === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classes' => SchoolClass::orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.form', [
            'student' => new Student(['is_active' => true]),
            'classes' => SchoolClass::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = $this->save($request);

        return redirect()->route('admin.students.index')
            ->with('success', "{$student->full_name} ditambahkan.");
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', [
            'student' => $student->load('schoolClass', 'parents.user'),
            'classes' => SchoolClass::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->save($request, $student);

        return redirect()->route('admin.students.index')
            ->with('success', "Perubahan data {$student->full_name} disimpan.");
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($this->students->hasHistory($student)) {
            return back()->with('error', "{$student->full_name} sudah punya riwayat presensi atau nilai, jadi tidak bisa dihapus. Nonaktifkan saja agar riwayatnya tetap tersimpan.");
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "{$student->full_name} dihapus.");
    }

    private function save(StudentRequest $request, ?Student $student = null): Student
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $student) {
                $student = $this->students->save($data, $student);

                if (! empty($data['parent_email'])) {
                    $this->students->linkParent($student, $data['parent_name'], $data['parent_email']);
                }

                return $student;
            });
        } catch (DataConflict $e) {
            throw ValidationException::withMessages(['parent_email' => $e->getMessage()]);
        }
    }
}
