<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_smtp_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider_label')->nullable()->index();
            $table->string('driver_type', 30)->default('smtp')->index();
            $table->string('mailer_name', 50)->default('smtp')->index();
            $table->string('host')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('username')->nullable();
            $table->text('encrypted_password')->nullable();
            $table->string('encryption', 20)->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->unsignedInteger('daily_limit')->nullable();
            $table->unsignedInteger('hourly_limit')->nullable();
            $table->unsignedInteger('warmup_limit')->nullable();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->string('status', 30)->default('active')->index();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamp('last_tested_at')->nullable()->index();
            $table->string('last_test_status', 30)->nullable()->index();
            $table->text('last_test_message')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_smtp_accounts');
    }
};