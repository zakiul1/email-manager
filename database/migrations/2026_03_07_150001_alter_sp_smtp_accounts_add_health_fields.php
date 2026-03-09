<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sp_smtp_accounts', function (Blueprint $table) {
            $table->unsignedInteger('failure_count')->default(0)->after('last_test_message');
            $table->unsignedInteger('success_count')->default(0)->after('failure_count');
            $table->timestamp('cooldown_until')->nullable()->after('success_count')->index();
        });

        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->unsignedInteger('attempt_count')->default(0)->after('status');
            $table->timestamp('retry_at')->nullable()->after('failed_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_campaign_messages', function (Blueprint $table) {
            $table->dropColumn(['attempt_count', 'retry_at']);
        });

        Schema::table('sp_smtp_accounts', function (Blueprint $table) {
            $table->dropColumn(['failure_count', 'success_count', 'cooldown_until']);
        });
    }
};