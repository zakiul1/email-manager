<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('export_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('export_id')->constrained()->cascadeOnDelete();

            $table->string('disk')->default('local');
            $table->string('path'); // storage path
            $table->string('filename');
            $table->unsignedBigInteger('size_bytes')->default(0);

            $table->timestamps();

            $table->index(['export_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_files');
    }
};