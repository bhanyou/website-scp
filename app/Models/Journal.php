<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'journal_date',
        'material',
        'activity',
        'notes',
        'photo_path',
        'submitted_at',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}