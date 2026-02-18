<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('category_email', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('import_batch_id');
            $table->index(['category_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::table('category_email', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'last_seen_at']);
            $table->dropColumn(['last_seen_at']);
        });
    }
};