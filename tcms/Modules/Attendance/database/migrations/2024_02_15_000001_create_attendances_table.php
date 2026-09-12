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

            // ── Tenant isolation ──
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Relationships ──
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('class_schedule_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // ── Attendance data ──
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->timestampTz('scanned_at')->nullable();

            $table->timestamps();

            // ── Indexes ──
            $table->index('user_id', 'attendances_user_id_idx');
            $table->index('student_id', 'attendances_student_id_idx');
            $table->index(['user_id', 'student_id'], 'attendances_user_student_idx');
            $table->index('scanned_at', 'attendances_scanned_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
