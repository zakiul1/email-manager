<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sp_smtp_accounts', function (Blueprint $table) {
            $table->timestamp('last_used_at')->nullable()->after('last_test_message');
        });
    }

    public function down(): void
    {
        Schema::table('sp_smtp_accounts', function (Blueprint $table) {
            $table->dropColumn('last_used_at');
        });
    }
};