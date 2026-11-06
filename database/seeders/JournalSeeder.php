<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Journal;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $schedule = Schedule::first();
        $teacher = Teacher::first();

        Journal::create([
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'journal_date' => '2026-08-20',
            'material' => 'Pengenalan dasar pemrograman',
            'activity' => 'Guru menjelaskan materi dan memberikan latihan kepada siswa.',
            'notes' => 'Siswa mengikuti pembelajaran dengan baik.',
            'photo_path' => null,
            'submitted_at' => now(),
        ]);
    }
}