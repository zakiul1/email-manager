<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->string('tracking_token', 100)->nullable()->unique()->after('recipient_email');
            $table->timestamp('delivered_at')->nullable()->after('sent_at')->index();
            $table->timestamp('opened_at')->nullable()->after('delivered_at')->index();
            $table->unsignedInteger('open_count')->default(0)->after('opened_at');
            $table->timestamp('clicked_at')->nullable()->after('open_count')->index();
            $table->unsignedInteger('click_count')->default(0)->after('clicked_at');
            $table->timestamp('unsubscribed_at')->nullable()->after('click_count')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_token',
                'delivered_at',
                'opened_at',
                'open_count',
                'clicked_at',
                'click_count',
                'unsubscribed_at',
            ]);
        });
    }
};