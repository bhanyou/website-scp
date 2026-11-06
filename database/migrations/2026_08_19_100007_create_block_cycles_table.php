<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_cycles', function (Blueprint $table) {
            $table->id();

            $table->enum('cycle', ['1', '2']);
            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->foreignId('locked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->index(['cycle', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_cycles');
    }
};