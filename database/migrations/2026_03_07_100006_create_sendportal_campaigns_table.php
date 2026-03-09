<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('template_id')->nullable()->constrained('sendportal_templates')->nullOnDelete();
            $table->foreignId('email_service_id')->nullable()->constrained('sendportal_email_services')->nullOnDelete();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->longText('html_content')->nullable();
            $table->longText('text_content')->nullable();
            $table->string('audience_type', 30)->nullable()->index();
            $table->string('audience_reference')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_campaigns');
    }
};