<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('parents')
                ->nullOnDelete();

            $table->date('permission_date');

            $table->enum('type', [
                'Izin',
                'Sakit'
            ]);

            $table->text('reason');

            $table->string('document_path')->nullable();

            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected'
            ])->default('Pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->text('verification_note')->nullable();

            $table->timestamps();

            $table->index([
                'student_id',
                'permission_date',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};