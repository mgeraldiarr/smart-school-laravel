<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DataConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function __construct(private TeacherService $teachers) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $teachers = Teacher::with('user')
            ->withCount('schedules')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('nuptk', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"))))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers', 'search'));
    }

    public function create(): View
    {
        return view('admin.teachers.form', ['teacher' => new Teacher()]);
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $teacher = $this->save($request);

        return redirect()->route('admin.teachers.index')
            ->with('success', "{$teacher->full_name} ditambahkan. Kata sandi awalnya password123.");
    }

    public function edit(Teacher $teacher): View
    {
        return view('admin.teachers.form', ['teacher' => $teacher->load('user')->loadCount('schedules')]);
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->save($request, $teacher);

        $message = "Perubahan data {$teacher->full_name} disimpan.";
        if ($request->boolean('reset_password')) {
            $message .= ' Kata sandinya kembali ke password123.';
        }

        return redirect()->route('admin.teachers.index')->with('success', $message);
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        if ($this->teachers->hasHistory($teacher)) {
            return back()->with('error', "{$teacher->full_name} masih punya jadwal, nilai, atau presensi yang tercatat. Menghapusnya akan ikut menghapus data itu, jadi nonaktifkan akunnya saja.");
        }

        $this->teachers->delete($teacher);

        return redirect()->route('admin.teachers.index')
            ->with('success', "{$teacher->full_name} dihapus.");
    }

    private function save(TeacherRequest $request, ?Teacher $teacher = null): Teacher
    {
        try {
            return DB::transaction(fn () => $this->teachers->save($request->validated(), $teacher));
        } catch (DataConflict $e) {
            throw ValidationException::withMessages(['nuptk' => $e->getMessage()]);
        }
    }
}
