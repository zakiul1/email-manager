<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_email', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_address_id')->constrained()->cascadeOnDelete();

            // For “duplicate handling per category”
            $table->unsignedInteger('times_added')->default(1);

            // Track how it entered
            $table->foreignId('import_batch_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->unique(['category_id', 'email_address_id']);
            $table->index(['category_id']);
            $table->index(['email_address_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_email');
    }
};