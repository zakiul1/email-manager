<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_campaigns', function (Blueprint $table) {
            $table->string('preheader')->nullable()->after('subject');
            $table->string('delivery_mode', 30)->default('draft')->after('status')->index();
            $table->foreignId('smtp_pool_id')->nullable()->after('email_service_id')->constrained('sp_smtp_pools')->nullOnDelete();
            $table->unsignedInteger('recipient_count')->default(0)->after('audience_reference');
            $table->unsignedInteger('sent_count')->default(0)->after('recipient_count');
            $table->unsignedInteger('failed_count')->default(0)->after('sent_count');
            $table->timestamp('queued_at')->nullable()->after('scheduled_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('smtp_pool_id');
            $table->dropColumn([
                'preheader',
                'delivery_mode',
                'recipient_count',
                'sent_count',
                'failed_count',
                'queued_at',
            ]);
        });
    }
};