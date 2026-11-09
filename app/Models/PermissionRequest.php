<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PermissionRequest extends Model
{
    protected $fillable = [
        'student_id',
        'parent_id',
        'permission_date',
        'end_date',
        'time_scope',
        'start_hour',
        'end_hour',
        'start_time',
        'end_time',
        'type',
        'reason',
        'document_path',
        'status',
        'verified_by',
        'verified_at',
        'verification_note',
    ];

    protected $casts = [
        'permission_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Menghitung total durasi hari izin (inklusif tanggal mulai dan selesai)
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->permission_date) {
            return 1;
        }

        $start = Carbon::parse($this->permission_date)->startOfDay();
        $end = Carbon::parse($this->end_date ?? $this->permission_date)->startOfDay();

        return max(1, $start->diffInDays($end) + 1);
    }

    /**
     * Label representasi rentang tanggal dan durasi
     * Contoh: "21/10/2026 s.d. 23/10/2026 (3 Hari)", "21/10/2026 (1 Hari)", atau "21/10/2026 (Jam ke 1 - 5)"
     */
    public function getDateRangeLabelAttribute(): string
    {
        if (!$this->permission_date) {
            return '-';
        }

        $startStr = $this->permission_date->format('d/m/Y');

        if ($this->time_scope === 'partial_day' && $this->start_hour && $this->end_hour) {
            return "{$startStr} (Jam ke {$this->start_hour} - {$this->end_hour})";
        }

        $endDate = $this->end_date ?? $this->permission_date;
        $endStr = $endDate->format('d/m/Y');
        $days = $this->duration_days;

        if ($startStr === $endStr || $days <= 1) {
            return "{$startStr} (1 Hari)";
        }

        return "{$startStr} s.d. {$endStr} ({$days} Hari)";
    }

    public function isPartialDay(): bool
    {
        return $this->time_scope === 'partial_day';
    }

    public function appliesToSchedule($schedule): bool
    {
        if ($this->time_scope !== 'partial_day') {
            return true;
        }

        if ($this->start_time && $this->end_time && $schedule->start_time && $schedule->end_time) {
            $schedStart = substr($schedule->start_time, 0, 5);
            $schedEnd = substr($schedule->end_time, 0, 5);
            $permStart = substr($this->start_time, 0, 5);
            $permEnd = substr($this->end_time, 0, 5);

            return !($schedEnd <= $permStart || $schedStart >= $permEnd);
        }

        return true;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SchoolParent::class, 'parent_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}