<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            $table->timestamp('subscribed_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamps();

            $table->unique(['user_id', 'system_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_modules');
    }
};
