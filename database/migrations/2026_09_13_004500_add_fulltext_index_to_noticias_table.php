<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            // Añadir índice en created_at y publicada para acelerar ordenamiento y filtrado
            $table->index('created_at', 'noticias_created_at_index');
            $table->index('publicada', 'noticias_publicada_index');
        });

        // Añadir índice FULLTEXT para MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE noticias ADD FULLTEXT noticias_search_fulltext(titulo, contenido)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE noticias DROP INDEX noticias_search_fulltext');
        }

        Schema::table('noticias', function (Blueprint $table) {
            $table->dropIndex('noticias_created_at_index');
            $table->dropIndex('noticias_publicada_index');
        });
    }
};
