<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->index(['status', 'sent_at'], 'sp_cm_status_sent_at_idx');
            $table->index(['campaign_id', 'sent_at'], 'sp_cm_campaign_sent_at_idx');
            $table->index(['campaign_id', 'smtp_account_id'], 'sp_cm_campaign_smtp_idx');
            $table->index(['smtp_account_id', 'status'], 'sp_cm_smtp_status_idx');
            $table->index(['subscriber_id', 'status'], 'sp_cm_subscriber_status_idx');
        });

        Schema::table('sendportal_campaigns', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'sp_campaign_status_created_idx');
            $table->index(['smtp_pool_id', 'status'], 'sp_campaign_pool_status_idx');
        });

        Schema::table('sendportal_campaign_audiences', function (Blueprint $table) {
            $table->index(['source_type', 'campaign_id'], 'sp_ca_source_campaign_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_campaign_audiences', function (Blueprint $table) {
            $table->dropIndex('sp_ca_source_campaign_idx');
        });

        Schema::table('sendportal_campaigns', function (Blueprint $table) {
            $table->dropIndex('sp_campaign_status_created_idx');
            $table->dropIndex('sp_campaign_pool_status_idx');
        });

        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->dropIndex('sp_cm_status_sent_at_idx');
            $table->dropIndex('sp_cm_campaign_sent_at_idx');
            $table->dropIndex('sp_cm_campaign_smtp_idx');
            $table->dropIndex('sp_cm_smtp_status_idx');
            $table->dropIndex('sp_cm_subscriber_status_idx');
        });
    }
};