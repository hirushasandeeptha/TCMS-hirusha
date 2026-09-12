<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // ── Tenant isolation ──
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Relationships ──
            $table->foreignId('student_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // ── Notification data ──
            $table->string('type');                           // e.g. fee_reminder, absence_alert, general
            $table->string('title');
            $table->text('body')->nullable();
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'in_app'])->default('in_app');
            $table->enum('status', ['draft', 'sent', 'failed', 'read'])->default('draft');
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('read_at')->nullable();

            // ── Dynamic metadata (delivery receipts, template vars, etc.) ──
            $table->jsonb('meta')->default('{}');

            $table->timestamps();

            // ── Indexes ──
            $table->index('user_id', 'notifications_user_id_idx');
            $table->index('student_id', 'notifications_student_id_idx');
            $table->index(['user_id', 'status'], 'notifications_user_status_idx');
            $table->index('created_at', 'notifications_created_at_idx');
        });

        // ── GIN index for JSONB meta queries ──
        DB::statement('CREATE INDEX notifications_meta_gin_idx ON notifications USING GIN (meta)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS notifications_meta_gin_idx');
        Schema::dropIfExists('notifications');
    }
};
