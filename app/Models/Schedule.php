<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    protected $fillable = [
        'block_cycle_id',
        'block_cycle',
        'teacher_id',
        'subject_id',
        'class_id',
        'class_group_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
        'start_period',
        'end_period',
        'jam_ke',
    ];

    /**
     * Konfigurasi Standar Jam Pelajaran FET Timetable (Jam 1 s/d 10)
     */
    public const PERIODS = [
        1  => ['label' => 'Jam ke-1',  'start' => '07:15', 'end' => '08:00'],
        2  => ['label' => 'Jam ke-2',  'start' => '08:00', 'end' => '08:45'],
        3  => ['label' => 'Jam ke-3',  'start' => '08:45', 'end' => '09:45'],
        4  => ['label' => 'Jam ke-4',  'start' => '09:45', 'end' => '10:30'],
        5  => ['label' => 'Jam ke-5',  'start' => '10:30', 'end' => '11:15'],
        6  => ['label' => 'Jam ke-6',  'start' => '11:15', 'end' => '12:30'],
        7  => ['label' => 'Jam ke-7',  'start' => '12:30', 'end' => '13:15'],
        8  => ['label' => 'Jam ke-8',  'start' => '13:15', 'end' => '14:00'],
        9  => ['label' => 'Jam ke-9',  'start' => '14:00', 'end' => '14:45'],
        10 => ['label' => 'Jam ke-10', 'start' => '14:45', 'end' => '15:30'],
    ];

    public static function getPeriods(?string $day = null): array
    {
        if ($day) {
            return LessonHour::getPeriodsForDay($day);
        }
        return self::PERIODS;
    }

    /**
     * Mendapatkan sesi jam ke- berapa dari start_time
     */
    public function getStartPeriodAttribute(): ?int
    {
        if (isset($this->attributes['start_period']) && $this->attributes['start_period'] !== null) {
            return (int) $this->attributes['start_period'];
        }
        if (isset($this->attributes['jam_ke']) && $this->attributes['jam_ke'] !== null) {
            return (int) $this->attributes['jam_ke'];
        }
        if (!$this->start_time) {
            return null;
        }
        $time = substr((string) $this->start_time, 0, 5);
        $periods = $this->day ? LessonHour::getPeriodsForDay($this->day) : self::PERIODS;

        foreach ($periods as $period => $data) {
            if ($data['start'] === $time) {
                return (int) $period;
            }
        }

        foreach (self::PERIODS as $period => $data) {
            if ($data['start'] === $time) {
                return (int) $period;
            }
        }

        return null;
    }

    /**
     * Mendapatkan sesi jam ke- berapa dari end_time
     */
    public function getEndPeriodAttribute(): ?int
    {
        if (isset($this->attributes['end_period']) && $this->attributes['end_period'] !== null) {
            return (int) $this->attributes['end_period'];
        }
        if (!$this->end_time) {
            return null;
        }
        $time = substr((string) $this->end_time, 0, 5);
        $periods = $this->day ? LessonHour::getPeriodsForDay($this->day) : self::PERIODS;

        foreach ($periods as $period => $data) {
            if ($data['end'] === $time) {
                return (int) $period;
            }
        }

        foreach (self::PERIODS as $period => $data) {
            if ($data['end'] === $time) {
                return (int) $period;
            }
        }

        return null;
    }

    /**
     * Label representasi sesi, misal: "Jam ke-1 s.d. 2 (07:00 - 08:30)"
     */
    public function getSessionLabelAttribute(): string
    {
        $startPeriod = $this->start_period;
        $endPeriod = $this->end_period;
        $startFormatted = $this->start_time ? substr((string) $this->start_time, 0, 5) : '-';
        $endFormatted = $this->end_time ? substr((string) $this->end_time, 0, 5) : '-';

        if ($startPeriod && $endPeriod) {
            if ($startPeriod === $endPeriod) {
                return "Jam ke-{$startPeriod} ({$startFormatted} - {$endFormatted})";
            }
            return "Jam ke-{$startPeriod} s.d. {$endPeriod} ({$startFormatted} - {$endFormatted})";
        } elseif ($startPeriod) {
            return "Jam ke-{$startPeriod} ({$startFormatted} - {$endFormatted})";
        }

        return "{$startFormatted} - {$endFormatted}";
    }

    public function blockCycle(): BelongsTo
    {
        return $this->belongsTo(BlockCycle::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function setClassGroupIdAttribute($value): void
    {
        $this->attributes['class_id'] = $value;
    }

    public function getClassGroupIdAttribute()
    {
        return $this->attributes['class_id'] ?? null;
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function todayJournal(): HasOne
    {
        return $this->hasOne(Journal::class)->whereDate('journal_date', \Carbon\Carbon::today());
    }
}