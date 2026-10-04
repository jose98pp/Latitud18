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
        // 1. PLANTILLAS (Estructuras reutilizables: portadas, interiores, etc.)
        Schema::create('periodico_plantillas', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('nombre', 150);
            $table->string('slug', 150)->index();
            $table->string('categoria', 50)->default('general')->index();
            $table->text('descripcion')->nullable();
            $table->string('preview_color', 30)->nullable()->default('#D71920');
            $table->boolean('is_custom')->default(false)->index();
            $table->longText('frames'); // JSON con los marcos/elementos predeterminados
            $table->json('configuracion')->nullable();
            $table->timestamps();
        });

        // 2. EDICIONES (Ejemplares semanales del periódico)
        Schema::create('periodico_ediciones', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('numero_edicion', 100);
            $table->string('fecha', 100);
            $table->string('titulo', 255)->nullable();
            $table->string('subtitulo', 255)->nullable();
            $table->string('slogan', 255)->nullable();
            $table->string('ciudad', 100)->default('Santa Cruz de la Sierra');
            $table->string('precio', 50)->default('Bs 7,00');
            $table->integer('num_paginas')->default(1);
            $table->boolean('publicada')->default(false)->index();
            $table->boolean('activa')->default(false)->index();
            $table->string('estado', 30)->default('borrador')->index();
            $table->dateTime('fecha_programada')->nullable();
            $table->dateTime('fecha_publicacion')->nullable();
            $table->string('pdf_url', 255)->nullable();
            $table->string('plantilla_id', 60)->nullable()->index();
            $table->timestamps();

            $table->foreign('plantilla_id')
                ->references('id')
                ->on('periodico_plantillas')
                ->onDelete('set null');
        });

        // 3. PÁGINAS (Páginas pertenecientes a cada edición)
        Schema::create('periodico_paginas', function (Blueprint $table) {
            $table->id();
            $table->string('edicion_id', 60)->index();
            $table->string('plantilla_id', 60)->nullable()->index();
            $table->integer('numero')->default(1);
            $table->string('nombre', 150);
            $table->string('seccion', 100)->nullable();
            $table->integer('ancho')->default(720);
            $table->integer('alto')->default(1040);
            $table->string('fondo_color', 30)->default('#ffffff');
            $table->json('configuracion')->nullable();
            $table->timestamps();

            $table->foreign('edicion_id')
                ->references('id')
                ->on('periodico_ediciones')
                ->onDelete('cascade');

            $table->foreign('plantilla_id')
                ->references('id')
                ->on('periodico_plantillas')
                ->onDelete('set null');

            $table->index(['edicion_id', 'numero']);
        });

        // 4. ELEMENTOS / FRAMES (Cajas de texto, titulares, fotos, cabeceras en cada página)
        Schema::create('periodico_elementos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagina_id')->constrained('periodico_paginas')->onDelete('cascade');
            $table->string('frame_id', 60)->index();
            $table->string('tipo', 50)->index(); // masthead, headline, article, image, quote, banner, divider, etc.
            $table->integer('x')->default(20);
            $table->integer('y')->default(20);
            $table->integer('w')->default(300);
            $table->integer('h')->default(150);
            $table->integer('z')->default(1);
            $table->longText('contenido')->nullable();
            $table->json('propiedades')->nullable(); // Columnas, tipografías, colores, URLs, kicker, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodico_elementos');
        Schema::dropIfExists('periodico_paginas');
        Schema::dropIfExists('periodico_ediciones');
        Schema::dropIfExists('periodico_plantillas');
    }
};
