<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a UNIQUE index on `periodico_plantillas.slug` (index name: uq_slug).
 *
 * IMPORTANT: Before running this migration, verify that there are no duplicate
 * slug values in the `periodico_plantillas` table. Duplicate slugs will cause
 * this migration to fail. Run the following query first:
 *
 *   SELECT slug, COUNT(*) as cnt
 *   FROM periodico_plantillas
 *   GROUP BY slug
 *   HAVING cnt > 1;
 *
 * If duplicates exist, resolve them (e.g., by appending a numeric suffix) before
 * applying this migration.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('periodico_plantillas', function (Blueprint $table) {
            $table->unique('slug', 'uq_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodico_plantillas', function (Blueprint $table) {
            $table->dropUnique('uq_slug');
        });
    }
};
