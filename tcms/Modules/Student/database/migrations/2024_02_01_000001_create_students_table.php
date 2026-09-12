<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // ── Tenant isolation ──
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Student info ──
            $table->string('student_code')->unique();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('qr_code_token')->nullable()->unique();

            // ── Dynamic metadata (school, guardian, etc.) ──
            $table->jsonb('custom_fields')->default('{}');

            $table->timestamps();

            // ── Indexes ──
            $table->index('user_id', 'students_user_id_idx');
        });

        // ── GIN index for JSONB custom_fields queries ──
        DB::statement('CREATE INDEX students_custom_fields_gin_idx ON students USING GIN (custom_fields)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS students_custom_fields_gin_idx');
        Schema::dropIfExists('students');
    }
};
