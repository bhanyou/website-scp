<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_hours', function (Blueprint $table) {
            $table->id();
            $table->string('day'); // 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'
            $table->integer('period_number'); // 1 s/d 10 (atau 1 s/d 8 di Jumat)
            $table->string('label'); // 'Jam ke-1', 'Jam ke-2', dll.
            $table->string('start_time', 5); // '08:00'
            $table->string('end_time', 5); // '08:35'
            $table->integer('duration_minutes')->default(40);
            $table->string('day_type')->default('default'); // 'default', 'upacara', 'non_upacara'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['day', 'day_type', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_hours');
    }
};
