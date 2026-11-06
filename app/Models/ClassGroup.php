<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ClassGroup (Rombongan Belajar / Kelas Siswa).
 * Alias kompatibilitas dan implementasi untuk tabel 'classes'.
 */
class ClassGroup extends SchoolClass
{
    protected $table = 'classes';

    /**
     * Relasi ke model Department (Jurusan).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relasi ke Guru BK (Bimbingan Konseling) pembina kelas.
     */
    public function bkTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'bk_teacher_id');
    }
}

