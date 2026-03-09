<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_subscribers', function (Blueprint $table) {
            $table->foreignId('email_address_id')
                ->nullable()
                ->after('id')
                ->constrained('email_addresses')
                ->nullOnDelete();

            $table->boolean('is_suppressed')->default(false)->after('status')->index();
            $table->timestamp('last_synced_at')->nullable()->after('unsubscribed_at');

            $table->unique('email_address_id');
            $table->index(['email_address_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_subscribers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('email_address_id');
            $table->dropColumn(['is_suppressed', 'last_synced_at']);
        });
    }
};