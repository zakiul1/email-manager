<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendportal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->index();
            $table->string('key', 150);
            $table->json('value_json')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['group', 'key'], 'sendportal_settings_group_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendportal_settings');
    }
};