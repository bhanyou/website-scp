<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Department (Alias kompatibilitas untuk Major).
 */
class Department extends Major
{
    protected $table = 'majors';

    /**
     * Relasi ke rombongan belajar menggunakan model ClassGroup.
     */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'department_id');
    }

    /**
     * Relasi ke rombongan belajar menggunakan model SchoolClass.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'department_id');
    }
}
