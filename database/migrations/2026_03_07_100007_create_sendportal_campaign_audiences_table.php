<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_campaign_audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('sendportal_campaigns')->cascadeOnDelete();
            $table->string('source_type', 30)->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_campaign_audiences');
    }
};