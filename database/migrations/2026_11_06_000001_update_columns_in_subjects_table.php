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
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'description')) {
                $table->dropColumn('description');
            }
            if (!Schema::hasColumn('subjects', 'category')) {
                $table->string('category')->default('Umum')->after('name');
            }
            if (!Schema::hasColumn('subjects', 'total_hours')) {
                $table->integer('total_hours')->default(2)->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('subjects', 'total_hours')) {
                $table->dropColumn('total_hours');
            }
            if (!Schema::hasColumn('subjects', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }
};

