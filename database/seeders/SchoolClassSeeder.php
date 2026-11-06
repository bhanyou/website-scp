<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\SchoolClass;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = Teacher::firstOrFail();

        SchoolClass::create([
            'name' => 'X RPL 1',
            'grade' => '10',
            'major' => 'RPL',
            'homeroom_teacher_id' => $teacher->id,
        ]);

        SchoolClass::create([
            'name' => 'X RPL 2',
            'grade' => '10',
            'major' => 'RPL',
            'homeroom_teacher_id' => $teacher->id,
        ]);

        SchoolClass::create([
            'name' => 'XI RPL 1',
            'grade' => '11',
            'major' => 'RPL',
            'homeroom_teacher_id' => $teacher->id,
        ]);
    }
}