<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_email_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver', 30)->default('smtp')->index();
            $table->string('mailer', 50)->default('smtp')->index();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->unsignedInteger('daily_limit')->nullable();
            $table->unsignedInteger('hourly_limit')->nullable();
            $table->unsignedInteger('messages_sent_today')->default(0);
            $table->unsignedInteger('messages_sent_hour')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_email_services');
    }
};