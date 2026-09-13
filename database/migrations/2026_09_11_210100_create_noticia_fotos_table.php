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
        Schema::create('noticia_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->onDelete('cascade');
            $table->string('image_path');
            $table->string('pie_de_foto', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticia_fotos');
    }
};
