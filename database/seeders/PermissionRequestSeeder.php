<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\SchoolParent;
use App\Models\User;
use App\Models\PermissionRequest;

class PermissionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();

        $parent = SchoolParent::first();

        $waka = User::where('role', 'waka')->first();

        PermissionRequest::create([
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'permission_date' => '2026-08-21',
            'type' => 'Izin',
            'reason' => 'Ada keperluan keluarga.',
            'document_path' => null,
            'status' => 'Approved',
            'verified_by' => $waka?->id,
            'verified_at' => now(),
            'verification_note' => 'Permohonan izin telah disetujui.',
        ]);
    }
}