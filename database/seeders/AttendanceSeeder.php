<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();

        $schedule = Schedule::first();

        Attendance::create([
            'student_id' => $student->id,
            'schedule_id' => $schedule->id,
            'date' => '2026-08-20',
            'status' => 'Hadir',
            'check_in' => '07:05',
            'note' => null,
        ]);
    }
}