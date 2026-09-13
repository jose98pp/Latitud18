<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            if (!Schema::hasColumn('noticias', 'publicar_en')) {
                $table->timestamp('publicar_en')->nullable()->after('urgente_hasta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            if (Schema::hasColumn('noticias', 'publicar_en')) {
                $table->dropColumn('publicar_en');
            }
        });
    }
};
