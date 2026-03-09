<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_smtp_pool_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_pool_id')->constrained('sp_smtp_pools')->cascadeOnDelete();
            $table->foreignId('smtp_account_id')->constrained('sp_smtp_accounts')->cascadeOnDelete();
            $table->unsignedInteger('weight')->default(100);
            $table->unsignedInteger('max_percent')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['smtp_pool_id', 'smtp_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_smtp_pool_accounts');
    }
};