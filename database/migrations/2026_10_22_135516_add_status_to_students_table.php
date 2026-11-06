<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan perubahan ke database.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('status')->default('aktif')->after('name');
            $table->unsignedBigInteger('class_id')->nullable()->change();
        });
    }

    /**
     * Membatalkan perubahan jika di-rollback.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->unsignedBigInteger('class_id')->nullable(false)->change();
        });
    }
};
