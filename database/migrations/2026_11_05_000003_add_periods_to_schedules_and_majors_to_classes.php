<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom start_period, end_period, jam_ke ke tabel schedules
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'start_period')) {
                $table->integer('start_period')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('schedules', 'end_period')) {
                $table->integer('end_period')->nullable()->after('start_period');
            }
            if (!Schema::hasColumn('schedules', 'jam_ke')) {
                $table->integer('jam_ke')->nullable()->after('end_period');
            }
        });

        // 2. Tambahkan kolom major_id dan department_id ke tabel classes
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'major_id')) {
                $table->foreignId('major_id')->nullable()->after('major')->constrained('majors')->nullOnDelete();
            }
            if (!Schema::hasColumn('classes', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('major_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'jam_ke')) {
                $table->dropColumn('jam_ke');
            }
            if (Schema::hasColumn('schedules', 'end_period')) {
                $table->dropColumn('end_period');
            }
            if (Schema::hasColumn('schedules', 'start_period')) {
                $table->dropColumn('start_period');
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'department_id')) {
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('classes', 'major_id')) {
                $table->dropForeign(['major_id']);
                $table->dropColumn('major_id');
            }
        });
    }
};

