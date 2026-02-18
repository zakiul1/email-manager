<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppression_entries', function (Blueprint $table) {
            $table->id();

            // global or domain
            $table->string('scope'); // global|domain

            // when scope=domain, domain is required
            $table->string('domain')->nullable();

            // when scope=global, email_address_id is required
            $table->foreignId('email_address_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('reason')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['scope']);
            $table->index(['domain']);
            $table->unique(['scope', 'domain', 'email_address_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppression_entries');
    }
};