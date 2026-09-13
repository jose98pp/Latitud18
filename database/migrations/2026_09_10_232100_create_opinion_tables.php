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
        Schema::create('columnistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cargo')->nullable(); // ej. "Analista Político", "Jefe de Redacción"
            $table->string('avatar')->nullable(); // URL o path de la foto
            $table->text('bio')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('articulos_opinion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('columnista_id')->constrained('columnistas')->onDelete('cascade');
            $table->string('tipo')->default('COLUMNA'); // EDITORIAL, COLUMNA, ANÁLISIS, COMENTARIO
            $table->string('titulo');
            $table->longText('contenido');
            $table->boolean('publicado')->default(true);
            $table->integer('vistas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos_opinion');
        Schema::dropIfExists('columnistas');
    }
};
