<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->timestamp('urgente_hasta')->nullable()->after('es_urgente');
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn('urgente_hasta');
        });
    }
};
