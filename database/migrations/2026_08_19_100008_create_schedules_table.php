<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('block_cycle_id')
                ->constrained('block_cycles')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);

            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

            $table->index([
                'block_cycle_id',
                'day',
                'start_time',
                'end_time'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};