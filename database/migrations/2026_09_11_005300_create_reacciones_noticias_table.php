<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reacciones_noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->onDelete('cascade');
            $table->string('tipo', 30); // me_informa, interesante, me_indigna, recomiendo
            $table->string('ip_address', 45);
            $table->timestamps();

            $table->unique(['noticia_id', 'tipo', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reacciones_noticias');
    }
};
