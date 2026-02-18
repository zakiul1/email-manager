<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('source_type'); // textarea|csv
            $table->string('original_filename')->nullable();

            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('inserted_rows')->default(0);

            $table->string('status')->default('queued'); // queued|processing|completed|failed
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'category_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};