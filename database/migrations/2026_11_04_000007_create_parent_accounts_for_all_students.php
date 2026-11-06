<?php

use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah tipe kolom role pada tabel users menjadi VARCHAR(30) agar mendukung role 'ortu'
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(30) NOT NULL DEFAULT 'siswa'");
        } catch (\Throwable $e) {
            // Fallback jika database bukan MySQL murni
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 30)->default('siswa')->change();
            });
        }

        // 2. Buat akun orang tua untuk setiap siswa yang ada saat ini
        $defaultPassword = Hash::make('12345678');
        $students = Student::with(['user', 'parent'])->get();

        foreach ($students as $student) {
            $studentUsername = $student->username ?: ($student->user?->username ?: $student->nis);
            $parentUsername = 'p' . $studentUsername;

            // Cek apakah akun parent user sudah ada
            $parentUser = User::where('username', $parentUsername)->first();
            if (!$parentUser) {
                $parentUser = User::create([
                    'name' => 'Orang Tua ' . $student->name,
                    'username' => $parentUsername,
                    'email' => null,
                    'password' => $student->user?->password ?? $defaultPassword,
                    'role' => 'ortu',
                    'phone' => $student->phone,
                ]);
            } else {
                // Pastikan rolenya ortu
                $parentUser->update(['role' => 'ortu']);
            }

            // Cek apakah record SchoolParent sudah ada
            $parentRecord = SchoolParent::where('user_id', $parentUser->id)->first();
            if (!$parentRecord) {
                $parentRecord = SchoolParent::create([
                    'user_id' => $parentUser->id,
                    'name' => 'Orang Tua ' . $student->name,
                    'phone' => $student->phone,
                    'address' => $student->address,
                ]);
            }

            // Tautkan student ke parent
            if ($student->parent_id !== $parentRecord->id) {
                $student->update(['parent_id' => $parentRecord->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu menghapus akun otomatis
    }
};

