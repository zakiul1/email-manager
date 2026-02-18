<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('row_number')->nullable();

            // Raw input email (before cleanup)
            $table->string('raw_email')->nullable();

            // Normalized email (lowercased + trimmed)
            $table->string('email')->nullable();
            $table->string('domain')->nullable();

            // valid|invalid|duplicate|suppressed|inserted
            $table->string('status')->index();

            // Invalid/suppressed reason etc.
            $table->string('reason')->nullable();

            // If we created/found a canonical email record
            $table->foreignId('email_address_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['import_batch_id']);
            $table->index(['email']);
            $table->index(['domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_items');
    }
};