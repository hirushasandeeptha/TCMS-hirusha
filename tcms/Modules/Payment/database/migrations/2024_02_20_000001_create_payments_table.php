<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ── Tenant isolation ──
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Relationships ──
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Payment data ──
            $table->date('month');                           // billing period (first of month)
            $table->decimal('amount', 10, 2);               // fee amount
            $table->enum('discount_type', ['full_free', 'half_free', 'none'])->default('none');
            $table->jsonb('receipt_meta')->default('{}');    // flexible receipt metadata

            $table->timestamps();

            // ── Indexes ──
            $table->index('user_id', 'payments_user_id_idx');
            $table->index('student_id', 'payments_student_id_idx');
            $table->unique(['student_id', 'month'], 'payments_student_month_unique');
        });

        // ── GIN index for JSONB receipt_meta queries ──
        DB::statement('CREATE INDEX payments_receipt_meta_gin_idx ON payments USING GIN (receipt_meta)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS payments_receipt_meta_gin_idx');
        Schema::dropIfExists('payments');
    }
};
