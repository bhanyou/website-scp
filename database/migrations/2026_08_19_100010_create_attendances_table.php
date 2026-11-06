<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained('schedules')
                ->cascadeOnDelete();

            $table->date('date');

            $table->enum('status', [
                'Hadir',
                'Sakit',
                'Izin',
                'Alpa'
            ]);

            $table->time('check_in')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            // Mencegah data presensi yang sama dicatat dua kali
            $table->unique([
                'student_id',
                'schedule_id',
                'date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};