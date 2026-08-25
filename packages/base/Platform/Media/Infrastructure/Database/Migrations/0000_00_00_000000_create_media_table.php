<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->string('storage_key', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size');
            $table->string('state', 32)->index();
            $table->string('upload_scope', 255);

            // Polymorphic relation + ordering
            $table->string('owner_type', 255)->nullable();
            $table->string('owner_id', 255)->nullable();
            $table->string('slot_name', 128)->nullable();
            $table->unsignedInteger('sort_order')->nullable();

            $table->timestamp('attached_at')->nullable();
            $table->timestamp('orphaned_at')->nullable();

            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'slot_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
