<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->boolean('destacada_hero')->default(false)->after('publicada');
            $table->boolean('es_investigacion')->default(false)->after('destacada_hero');
            $table->boolean('es_urgente')->default(false)->after('es_investigacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn(['destacada_hero', 'es_investigacion', 'es_urgente']);
        });
    }
};
