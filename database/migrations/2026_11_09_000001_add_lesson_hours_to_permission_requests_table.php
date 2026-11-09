<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->string('time_scope')->default('full_day')->after('end_date'); // 'full_day' atau 'partial_day'
            $table->unsignedSmallInteger('start_hour')->nullable()->after('time_scope'); // Jam ke-...
            $table->unsignedSmallInteger('end_hour')->nullable()->after('start_hour');   // s/d Jam ke-...
            $table->time('start_time')->nullable()->after('end_hour'); // Waktu mulai jika ada
            $table->time('end_time')->nullable()->after('start_time');   // Waktu selesai jika ada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->dropColumn(['time_scope', 'start_hour', 'end_hour', 'start_time', 'end_time']);
        });
    }
};

