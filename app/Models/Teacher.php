<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'username',
        'name',
        'mapel',
        'phone',
        'address',
    ];

    /**
     * Alias atribut mapel / subject
     */
    public function getSubjectAttribute(): ?string
    {
        return $this->mapel;
    }

    public function setSubjectAttribute(?string $value): void
    {
        $this->attributes['mapel'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function bkClasses(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'bk_teacher_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function isBkTeacher(): bool
    {
        if ($this->bkClasses()->exists()) {
            return true;
        }

        if (!empty($this->mapel) && (stripos($this->mapel, 'BK') !== false || stripos($this->mapel, 'KONSELING') !== false)) {
            return true;
        }

        return $this->schedules()->whereHas('subject', function ($q) {
            $q->where('name', 'like', '%Bimbingan%')
              ->orWhere('name', 'like', '%Konseling%')
              ->orWhere('name', 'like', '%BK%');
        })->exists();
    }
}