<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Status untuk siswa yang belum punya catatan presensi. Dipakai bersama
     * oleh sesi guru dan monitoring wali agar aplikasi cukup mengenal satu nama.
     */
    public const NOT_RECORDED = 'Belum_Absen';

    protected $fillable = [
        'student_id',
        'schedule_id',
        'date',
        'check_in_time',
        'status',
        'method',
        'input_by_teacher_id',
        'captured_photo_url',
        'confidence_score',
    ];
    
    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schedule() {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function verifiedByTeacher() {
        return $this->belongsTo(Teacher::class, 'input_by_teacher_id');
    }
}
