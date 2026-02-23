<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('domain_unsubscribes', function (Blueprint $table) {
            // New structure
            $table->string('type', 20)->nullable()->after('id');   // domain | extension
            $table->string('value', 255)->nullable()->after('type');

            // Index for faster search
            $table->index(['type', 'value'], 'domain_unsubscribes_type_value_idx');
        });

        /**
         * Backfill existing `domain` column into new fields
         * - If your table already has `domain` column, we move it into type/value.
         * - Default: type=domain, value=domain
         */
        if (Schema::hasColumn('domain_unsubscribes', 'domain')) {
            DB::table('domain_unsubscribes')->whereNull('type')->update([
                'type' => 'domain',
            ]);

            DB::statement("UPDATE domain_unsubscribes SET value = domain WHERE value IS NULL AND domain IS NOT NULL");
        } else {
            // If there is no domain column, just set defaults to avoid nulls
            DB::table('domain_unsubscribes')->whereNull('type')->update(['type' => 'domain']);
        }

        // Make columns non-nullable after backfill
        Schema::table('domain_unsubscribes', function (Blueprint $table) {
            $table->string('type', 20)->nullable(false)->change();
            $table->string('value', 255)->nullable(false)->change();
        });

        // Add unique constraint to prevent duplicates
        Schema::table('domain_unsubscribes', function (Blueprint $table) {
            $table->unique(['type', 'value'], 'domain_unsubscribes_type_value_unique');
        });

        /**
         * Optional: keep old domain column for compatibility or drop it.
         * Recommended: drop it after you update code to use type/value.
         */
        if (Schema::hasColumn('domain_unsubscribes', 'domain')) {
            Schema::table('domain_unsubscribes', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
    }

    public function down(): void
    {
        // Recreate old `domain` column (best-effort) and remove new structure
        Schema::table('domain_unsubscribes', function (Blueprint $table) {
            if (!Schema::hasColumn('domain_unsubscribes', 'domain')) {
                $table->string('domain', 255)->nullable()->after('id');
            }
        });

        // Copy back only domain rows (type=domain)
        DB::statement("UPDATE domain_unsubscribes SET domain = value WHERE type = 'domain'");

        Schema::table('domain_unsubscribes', function (Blueprint $table) {
            $table->dropUnique('domain_unsubscribes_type_value_unique');
            $table->dropIndex('domain_unsubscribes_type_value_idx');

            $table->dropColumn(['type', 'value']);
        });
    }
};