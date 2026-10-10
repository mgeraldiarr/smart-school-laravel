<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'full_name',
        'birth_date',
        'class_id',
        'photo_url',
        'face_embedding',
        'is_active',
    ];

    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function parents() {
        return $this->belongsToMany(SchoolParent::class, 'parent_students', 'student_id', 'parent_id');
    }

    public function attendances() {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function grades() {
        return $this->hasMany(Grade::class, 'student_id');
    }
}
