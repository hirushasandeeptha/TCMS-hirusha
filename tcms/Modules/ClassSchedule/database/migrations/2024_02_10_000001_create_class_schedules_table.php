<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            // ── Tenant isolation ──
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Schedule data ──
            $table->string('name');                            // e.g. "Grade 10 Math - Monday"
            $table->string('subject')->nullable();
            $table->enum('day_of_week', [
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            ]);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);

            // ── Dynamic metadata (recurring rules, max capacity, etc.) ──
            $table->jsonb('meta')->default('{}');

            $table->timestamps();

            // ── Indexes ──
            $table->index('user_id', 'class_schedules_user_id_idx');
            $table->index(['user_id', 'day_of_week'], 'class_schedules_user_day_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
