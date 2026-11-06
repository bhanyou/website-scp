<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlockCycle;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Room;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $blockCycle = BlockCycle::where('is_active', true)->first();

        $teacher = Teacher::first();

        $subjects = Subject::all();

        $classes = SchoolClass::all();

        $rooms = Room::all();

        Schedule::create([
            'block_cycle_id' => $blockCycle->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects[0]->id,
            'class_id' => $classes[0]->id,
            'room_id' => $rooms[0]->id,
            'day' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:30',
        ]);

        Schedule::create([
            'block_cycle_id' => $blockCycle->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects[1]->id,
            'class_id' => $classes[0]->id,
            'room_id' => $rooms[0]->id,
            'day' => 'Senin',
            'start_time' => '08:30',
            'end_time' => '10:00',
        ]);

        Schedule::create([
            'block_cycle_id' => $blockCycle->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects[2]->id,
            'class_id' => $classes[1]->id,
            'room_id' => $rooms[1]->id,
            'day' => 'Selasa',
            'start_time' => '07:00',
            'end_time' => '08:30',
        ]);

        Schedule::create([
            'block_cycle_id' => $blockCycle->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects[3]->id,
            'class_id' => $classes[1]->id,
            'room_id' => $rooms[1]->id,
            'day' => 'Selasa',
            'start_time' => '08:30',
            'end_time' => '10:00',
        ]);
    }
}