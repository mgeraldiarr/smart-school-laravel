<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;

class GradeController extends Controller
{
    /**
     * 1. Ambil Rekap Nilai Siswa (Filter per Kelas & Mapel)
     * GET /api/teacher/grades?class_id=1&subject_id=1
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'class_id'        => 'nullable|exists:school_classes,id',
            'subject_id'      => 'nullable|exists:subjects,id',
            'assessment_type' => 'nullable|in:Tugas,UTS,UAS',
            'student_id'      => 'nullable|exists:students,id',
        ]);

        // Guru hanya melihat nilai yang ia input sendiri.
        $query = Grade::with(['student', 'subject', 'schoolClass', 'teacher'])
            ->where('teacher_id', $this->currentTeacher()->id)
            ->latest('assessment_date');

        if (!empty($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
        }

        if (!empty($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }

        if (!empty($validated['assessment_type'])) {
            $query->where('assessment_type', $validated['assessment_type']);
        }

        if (!empty($validated['student_id'])) {
            $query->where('student_id', $validated['student_id']);
        }

        $grades = $query->get()->map(function ($g) {
            return [
                'id'              => $g->id,
                'student_id'      => $g->student_id,
                'student_nis'     => $g->student->nis ?? '-',
                'student_name'    => $g->student->full_name ?? '-',
                'class'           => $g->schoolClass->name ?? '-',
                'subject'         => $g->subject->name ?? '-',
                'teacher'         => $g->teacher->full_name ?? '-',
                'assessment_type' => $g->assessment_type,
                'title'           => $g->title,
                'score'           => (float) $g->score,
                'teacher_notes'   => $g->teacher_notes,
                'assessment_date' => $g->assessment_date,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total'  => $grades->count(),
            'data'   => $grades,
        ], 200);
    }

    /**
     * 2. Input Nilai Baru oleh Guru
     * POST /api/teacher/grades
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'subject_id'      => 'required|exists:subjects,id',
            'class_id'        => 'required|exists:school_classes,id',
            'assessment_type' => 'required|in:Tugas,UTS,UAS',
            'title'           => 'required|string|max:100', // misal: "Tugas 1 Aljabar"
            'score'           => 'required|numeric|min:0|max:100',
            'teacher_notes'   => 'nullable|string|max:500',
            'assessment_date' => 'nullable|date',
        ]);

        $teacherId = $this->currentTeacher()->id;

        // Guru hanya boleh menilai mapel yang ia ajar di kelas tersebut.
        $teachesSubject = Schedule::where('teacher_id', $teacherId)
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();
        if (! $teachesSubject) {
            $this->fail('Anda tidak mengajar mata pelajaran ini di kelas tersebut.', 403);
        }

        $student = Student::findOrFail($validated['student_id']);
        if ((int) $student->class_id !== (int) $validated['class_id']) {
            $this->fail("Siswa {$student->full_name} bukan anggota kelas ini.", 422);
        }

        $grade = Grade::create([
            'student_id'      => $validated['student_id'],
            'subject_id'      => $validated['subject_id'],
            'teacher_id'      => $teacherId,
            'class_id'        => $validated['class_id'],
            'assessment_type' => $validated['assessment_type'],
            'title'           => $validated['title'],
            'score'           => $validated['score'],
            'teacher_notes'   => $validated['teacher_notes'] ?? null,
            'assessment_date' => $validated['assessment_date'] ?? Carbon::today()->toDateString(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Nilai {$validated['assessment_type']} untuk {$student->full_name} berhasil disimpan.",
            'data'    => $grade,
        ], 201);
    }

    /**
     * 3. Perbarui Nilai Siswa (Edit Nilai / Catatan)
     * PUT /api/teacher/grades/{id}
     */
    public function update(Request $request, int $id)
    {
        $grade = $this->ownedGrade($id);

        $validated = $request->validate([
            'title'           => 'sometimes|string|max:100',
            'score'           => 'sometimes|numeric|min:0|max:100',
            'teacher_notes'   => 'nullable|string|max:500',
            'assessment_date' => 'nullable|date',
        ]);

        $grade->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data nilai berhasil diperbarui.',
            'data'    => $grade,
        ], 200);
    }

    /**
     * 4. Hapus Rekor Nilai
     * DELETE /api/teacher/grades/{id}
     */
    public function destroy(int $id)
    {
        $grade = $this->ownedGrade($id);
        $grade->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data nilai berhasil dihapus.'
        ], 200);
    }

    private function currentTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;
        if (! $teacher) {
            $this->fail('Data profil guru tidak ditemukan.', 404);
        }

        return $teacher;
    }

    /** Nilai hanya bisa diubah atau dihapus oleh guru yang menginputnya. */
    private function ownedGrade(int $id): Grade
    {
        $grade = Grade::findOrFail($id);
        if ((int) $grade->teacher_id !== (int) $this->currentTeacher()->id) {
            $this->fail('Nilai ini diinput oleh guru lain.', 403);
        }

        return $grade;
    }

    private function fail(string $message, int $status): never
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $status));
    }
}
