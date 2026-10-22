<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('seccion_deportiva', 32)->nullable()->index();
        });
        Schema::table('articulos_opinion', function (Blueprint $table) {
            $table->boolean('es_deportiva')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('articulos_opinion', fn (Blueprint $table) => $table->dropColumn('es_deportiva'));
        Schema::table('noticias', fn (Blueprint $table) => $table->dropColumn('seccion_deportiva'));
    }
};
