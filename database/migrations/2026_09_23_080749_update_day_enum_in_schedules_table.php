<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE schedules MODIFY COLUMN day VARCHAR(50) NOT NULL");

        DB::table('schedules')->where('day', 'Monday')->update(['day' => 'Senin']);
        DB::table('schedules')->where('day', 'Tuesday')->update(['day' => 'Selasa']);
        DB::table('schedules')->where('day', 'Wednesday')->update(['day' => 'Rabu']);
        DB::table('schedules')->where('day', 'Thursday')->update(['day' => 'Kamis']);
        DB::table('schedules')->where('day', 'Friday')->update(['day' => 'Jumat']);

        DB::statement("ALTER TABLE schedules MODIFY COLUMN day ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE schedules MODIFY COLUMN day VARCHAR(50) NOT NULL");

        DB::table('schedules')->where('day', 'Senin')->update(['day' => 'Monday']);
        DB::table('schedules')->where('day', 'Selasa')->update(['day' => 'Tuesday']);
        DB::table('schedules')->where('day', 'Rabu')->update(['day' => 'Wednesday']);
        DB::table('schedules')->where('day', 'Kamis')->update(['day' => 'Thursday']);
        DB::table('schedules')->where('day', 'Jumat')->update(['day' => 'Friday']);

        DB::statement("ALTER TABLE schedules MODIFY COLUMN day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL");
    }
};
