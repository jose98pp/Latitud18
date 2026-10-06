<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `periodico_plantillas_edicion` table which stores
     * the 12-slot mapping that defines a Plantilla_de_Edicion.
     * Each mapping entry has the format: {"slot": N, "plantilla_id": "..."|null}
     *
     * Validates: Requirements 5.4, 5.5
     */
    public function up(): void
    {
        Schema::create('periodico_plantillas_edicion', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            // Stores exactly 12 entries: [{"slot":1,"plantilla_id":"tpl_portada_clasica"}, ..., {"slot":12,"plantilla_id":null}]
            $table->json('mapping');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodico_plantillas_edicion');
    }
};
