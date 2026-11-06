<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        Subject::updateOrCreate(
            ['code' => 'MTK'],
            [
                'name' => 'Matematika',
                'category' => 'Umum',
                'total_hours' => 4,
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'BIND'],
            [
                'name' => 'Bahasa Indonesia',
                'category' => 'Umum',
                'total_hours' => 3,
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'BIG'],
            [
                'name' => 'Bahasa Inggris',
                'category' => 'Umum',
                'total_hours' => 3,
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'PPL'],
            [
                'name' => 'Pemrograman Perangkat Lunak',
                'category' => 'Kejuruan',
                'total_hours' => 6,
            ]
        );
    }
}
