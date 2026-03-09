<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_subscriber_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('sendportal_subscribers')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('sendportal_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subscriber_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_subscriber_tag');
    }
};