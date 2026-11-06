<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('permission_date');
        });

        // Set default end_date = permission_date untuk data yang sudah ada sebelumnya
        DB::statement('UPDATE permission_requests SET end_date = permission_date WHERE end_date IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
