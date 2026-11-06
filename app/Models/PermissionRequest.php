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
     * Contoh: "21/10/2026 s.d. 23/10/2026 (3 Hari)" atau "21/10/2026 (1 Hari)"
     */
    public function getDateRangeLabelAttribute(): string
    {
        if (!$this->permission_date) {
            return '-';
        }

        $startStr = $this->permission_date->format('d/m/Y');
        $endDate = $this->end_date ?? $this->permission_date;
        $endStr = $endDate->format('d/m/Y');
        $days = $this->duration_days;

        if ($startStr === $endStr || $days <= 1) {
            return "{$startStr} (1 Hari)";
        }

        return "{$startStr} s.d. {$endStr} ({$days} Hari)";
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