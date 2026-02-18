<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppression_entries', function (Blueprint $table) {
            $table->index(['scope', 'email_address_id']);
            $table->index(['scope', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::table('suppression_entries', function (Blueprint $table) {
            $table->dropIndex(['scope', 'email_address_id']);
            $table->dropIndex(['scope', 'domain']);
        });
    }
};