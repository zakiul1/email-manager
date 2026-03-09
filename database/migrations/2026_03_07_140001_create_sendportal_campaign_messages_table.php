<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('sendportal_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained('sendportal_subscribers')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('sendportal_templates')->nullOnDelete();
            $table->foreignId('smtp_account_id')->nullable()->constrained('sp_smtp_accounts')->nullOnDelete();
            $table->foreignId('smtp_pool_id')->nullable()->constrained('sp_smtp_pools')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->string('recipient_email')->index();
            $table->string('subject')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->timestamp('queued_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('failure_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'subscriber_id']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_campaign_messages');
    }
};