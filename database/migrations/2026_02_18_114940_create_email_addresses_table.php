<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_addresses', function (Blueprint $table) {
            $table->id();

            // canonical email (lowercased)
            $table->string('email')->unique();
            $table->string('local_part');
            $table->string('domain');

            // validation state used by importer
            $table->boolean('is_valid')->default(true);
            $table->string('invalid_reason')->nullable();

            $table->timestamps();

            $table->index(['domain']);
            $table->index(['is_valid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_addresses');
    }
};