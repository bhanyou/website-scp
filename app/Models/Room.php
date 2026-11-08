<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'name',
        'type',
        'capacity',
    ];

    /**
     * Normalisasi nama ruangan: area olahraga selalu berlabel 'OR'.
     */
    public function getNameAttribute($value): string
    {
        $val = trim((string)$value);
        if (preg_match('/^OR\s*\d*$/i', $val) || preg_match('/^LAPANGAN\s*OR/i', $val)) {
            return 'OR';
        }
        return $val;
    }

    /**
     * Label tipe ruangan terformat ('Praktik', 'Olahraga', 'Teori').
     */
    public function getTypeLabelAttribute(): string
    {
        $type = trim((string)($this->attributes['type'] ?? ''));
        if (!empty($type) && in_array(ucfirst(strtolower($type)), ['Praktik', 'Olahraga', 'Teori'])) {
            return ucfirst(strtolower($type));
        }

        $upper = strtoupper($this->name ?? '');

        if (str_contains($upper, 'LAB') || str_contains($upper, 'BENGKEL') || str_contains($upper, 'PRAKTIK') || str_contains($upper, 'KOMPUTER')) {
            return 'Praktik';
        }
        if (str_contains($upper, 'OR') || str_contains($upper, 'LAPANGAN') || str_contains($upper, 'OLAHRAGA')) {
            return 'Olahraga';
        }

        return 'Teori';
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}