<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sendportal_templates', function (Blueprint $table) {
            $table->string('preheader')->nullable()->after('subject');
            $table->string('status', 30)->default('draft')->after('editor')->index();
            $table->unsignedInteger('usage_count')->default(0)->after('status');
            $table->text('version_notes')->nullable()->after('usage_count');
            $table->json('builder_meta')->nullable()->after('version_notes');
            $table->timestamp('last_test_sent_at')->nullable()->after('builder_meta')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sendportal_templates', function (Blueprint $table) {
            $table->dropColumn([
                'preheader',
                'status',
                'usage_count',
                'version_notes',
                'builder_meta',
                'last_test_sent_at',
            ]);
        });
    }
};