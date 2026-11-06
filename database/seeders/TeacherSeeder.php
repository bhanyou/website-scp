<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::where('email', 'guru@sch.com')->first();

        Teacher::create([
            'user_id' => $guru->id,
            'nip' => '198001010001',
            'name' => 'Guru',
            'phone' => '081234567891',
            'address' => 'Alamat Guru',
        ]);
    }
}