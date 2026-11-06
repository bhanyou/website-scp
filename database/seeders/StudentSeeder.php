<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\SchoolParent;
use App\Models\SchoolClass;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil akun siswa
        $user = User::where('email', 'siswa@sch.com')->firstOrFail();

        // Ambil data orang tua
        $parent = SchoolParent::firstOrFail();

        // Ambil guru untuk wali kelas
        $teacher = Teacher::firstOrFail();

        // Ambil kelas pertama
        $class = SchoolClass::first();

        // Jika belum ada kelas, buat satu kelas
        if (!$class) {
            $class = SchoolClass::create([
                'name' => 'X RPL 1',
                'grade' => '10',
                'major' => 'RPL',
                'homeroom_teacher_id' => $teacher->id,
            ]);
        }

        // Buat data siswa
        Student::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'parent_id' => $parent->id,
            'nis' => '12345678',
            'name' => 'Siswa',
            'phone' => '081234567893',
            'address' => 'Alamat Siswa',
        ]);
    }
}