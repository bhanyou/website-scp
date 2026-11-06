<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'key' => 'school_name',
            'value' => 'Sekolah',
        ]);

        Setting::create([
            'key' => 'academic_year',
            'value' => '2026/2027',
        ]);

        Setting::create([
            'key' => 'attendance_start_time',
            'value' => '07:00',
        ]);

        Setting::create([
            'key' => 'attendance_end_time',
            'value' => '15:00',
        ]);
    }
}