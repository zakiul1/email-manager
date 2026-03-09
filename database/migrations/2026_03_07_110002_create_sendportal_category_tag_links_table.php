<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_category_tag_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained('sendportal_tags')->nullOnDelete();
            $table->boolean('sync_enabled')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->unsignedInteger('last_synced_total')->default(0);
            $table->unsignedInteger('last_synced_subscribed')->default(0);
            $table->unsignedInteger('last_synced_suppressed')->default(0);
            $table->timestamps();

            $table->unique('category_id');
            $table->unique('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_category_tag_links');
    }
};