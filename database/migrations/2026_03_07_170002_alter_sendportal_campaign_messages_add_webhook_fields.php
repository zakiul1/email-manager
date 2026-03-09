<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->string('provider_message_id')->nullable()->after('tracking_token')->index();
            $table->string('provider_event', 50)->nullable()->after('provider_message_id')->index();
            $table->timestamp('bounced_at')->nullable()->after('clicked_at')->index();
            $table->timestamp('complained_at')->nullable()->after('bounced_at')->index();
            $table->json('provider_payload')->nullable()->after('meta');
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->dropColumn([
                'provider_message_id',
                'provider_event',
                'bounced_at',
                'complained_at',
                'provider_payload',
            ]);
        });
    }
};