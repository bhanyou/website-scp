<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $students = DB::table('students')->select('id', 'user_id', 'nis', 'name', 'username')->get();
        $existingUsernames = DB::table('users')->whereNotNull('username')->pluck('username')->flip()->toArray();

        foreach ($students as $student) {
            // Jika username kosong atau sama dengan NIS, ganti ke nama panggilan
            if (empty($student->username) || $student->username === $student->nis) {
                $nameParts = preg_split('/\s+/', trim($student->name));
                $firstWord = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nameParts[0] ?? ''));
                if (empty($firstWord) || strlen($firstWord) < 2) {
                    $firstWord = 'siswa' . $student->id;
                }

                $candUsername = $firstWord;
                $suffix = 1;
                while (isset($existingUsernames[$candUsername])) {
                    $candUsername = $firstWord . $suffix;
                    $suffix++;
                }
                $existingUsernames[$candUsername] = true;

                // Update student table
                DB::table('students')->where('id', $student->id)->update(['username' => $candUsername]);

                // Update users table
                if ($student->user_id) {
                    DB::table('users')->where('id', $student->user_id)->update(['username' => $candUsername]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};

