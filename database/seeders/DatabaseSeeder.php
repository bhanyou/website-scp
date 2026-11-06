<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TeacherSeeder::class,
            SchoolParentSeeder::class,
            SubjectSeeder::class,
            RoomSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
            BlockCycleSeeder::class,
            ScheduleSeeder::class,
            JournalSeeder::class,
            AttendanceSeeder::class,
            PermissionRequestSeeder::class,
            SettingSeeder::class,
            BkClassAssignmentSeeder::class,
        ]);
    }
}