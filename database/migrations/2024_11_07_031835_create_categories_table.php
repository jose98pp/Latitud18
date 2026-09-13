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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Esto crea una columna 'id' de tipo BIGINT AUTO_INCREMENT
            $table->string('name'); // Esto crea una columna 'name' de tipo VARCHAR(255) NOT NULL
            $table->timestamps(); // Esto crea las columnas 'created_at' y 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
