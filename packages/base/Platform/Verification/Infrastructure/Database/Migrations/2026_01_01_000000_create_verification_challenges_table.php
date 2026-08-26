<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->string('target_type');
            $table->string('target_key');
            $table->string('purpose');
            $table->string('code_hash');
            $table->integer('attempts');
            $table->integer('max_attempts');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamps();

            $table->boolean('is_active')->nullable();
            $table->unique(['target_type', 'target_key', 'purpose', 'is_active'], 'verification_challenges_active_unique');
            $table->index(['target_type', 'target_key', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_challenges');
    }
};
