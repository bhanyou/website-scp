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
        $students = DB::table('students')->select('id', 'user_id')->orderBy('id', 'asc')->get();

        $index = 1;
        foreach ($students as $student) {
            $username = 'siswa' . $index;

            DB::table('students')->where('id', $student->id)->update([
                'username' => $username,
            ]);

            if ($student->user_id) {
                DB::table('users')->where('id', $student->user_id)->update([
                    'username' => $username,
                ]);
            }

            $index++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};

