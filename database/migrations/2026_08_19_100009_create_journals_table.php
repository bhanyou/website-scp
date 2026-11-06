<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->date('journal_date');

            $table->text('material');
            $table->text('activity')->nullable();
            $table->text('notes')->nullable();

            $table->string('photo_path')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique([
                'schedule_id',
                'journal_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};