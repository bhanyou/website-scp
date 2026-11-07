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
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'block_cycle')) {
                $table->unsignedTinyInteger('block_cycle')->default(1)->after('block_cycle_id')->index();
            }
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE schedules SET block_cycle = COALESCE(block_cycle_id, 1)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'block_cycle')) {
                $table->dropColumn('block_cycle');
            }
        });
    }
};
