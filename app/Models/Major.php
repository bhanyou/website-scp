<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'department_id');
    }

    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'department_id');
    }
}
