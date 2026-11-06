<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade',
        'major',
        'major_id',
        'department_id',
        'homeroom_teacher_id',
        'bk_teacher_id',
    ];

    /**
     * Kamus pemetaan nama jurusan resmi SMK Negeri 1 Bantul.
     */
    public const DEPARTMENT_MAP = [
        'RPL' => 'Rekayasa Perangkat Lunak',
        'TKJ' => 'Teknik Komputer dan Jaringan',
        'DKV' => 'Desain Komunikasi Visual',
        'AK'  => 'Akuntansi',
        'AKL' => 'Akuntansi',
        'LPS' => 'Layanan Perbankan Syariah',
        'MP'  => 'Manajemen Perkantoran',
        'BR'  => 'Bisnis Retail',
        'BD'  => 'Bisnis Digital',
    ];

    /**
     * Ekstrak kode jurusan dan otomatis lakukan firstOrCreate pada tabel jurusan (Department).
     *
     * @param string $className Contoh: 'X RPL 1', 'XI TKJ 2', 'XII DKV 1'
     * @return Department|null
     */
    public static function resolveDepartmentFromName(string $className): ?Department
    {
        if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', trim($className), $matches)) {
            $code = strtoupper(trim($matches[2]));

            // Normalisasi variasi kode AKL ke AK resmi
            if ($code === 'AKL') {
                $code = 'AK';
            }

            $fullName = self::DEPARTMENT_MAP[$code] ?? ($code . ' Program');

            return Department::firstOrCreate(
                ['code' => $code],
                ['name' => $fullName]
            );
        }

        return null;
    }

    /**
     * Lifecycle boot event untuk auto-sync jurusan saat kelas dibuat/diupdate.
     */
    protected static function booted()
    {
        static::saving(function ($classModel) {
            if (!empty($classModel->name)) {
                if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', trim($classModel->name), $matches)) {
                    $grade = strtoupper(trim($matches[1]));
                    $code = strtoupper(trim($matches[2]));
                    if ($code === 'AKL') {
                        $code = 'AK';
                    }

                    $department = self::resolveDepartmentFromName($classModel->name);
                    if ($department) {
                        $classModel->department_id = $department->id;
                        $classModel->major_id = $department->id;
                        if (empty($classModel->major)) {
                            $classModel->major = $code;
                        }
                    }

                    if (empty($classModel->grade)) {
                        $classModel->grade = $grade;
                    }
                }
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function majorRelation(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function bkTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'bk_teacher_id');
    }
}
