<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    public const CATEGORIES = [
        'Kejuruan' => 'Kejuruan',
        'Umum' => 'Umum',
        'Olahraga' => 'Olahraga',
    ];

    protected $fillable = [
        'code',
        'name',
        'category',
        'total_hours',
    ];

    protected $casts = [
        'total_hours' => 'integer',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
