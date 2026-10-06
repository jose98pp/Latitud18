<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoElemento;
use App\Models\PeriodicoPlantilla;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlantillaAplicarTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected PeriodicoEdicion $edicion;
    protected PeriodicoPagina $pagina;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@latitud18.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('secret123'), 'role' => 'admin']
        );

        $this->edicion = PeriodicoEdicion::create([
            'id' => 'ed-test-' . time(),
            'numero_edicion' => 'Edición Test',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Test Plantillas',
            'num_paginas' => 1,
            'estado' => 'borrador',
            'publicada' => false
        ]);

        $this->pagina = PeriodicoPagina::create([
            'edicion_id' => $this->edicion->id,
            'numero' => 1,
            'seccion' => 'Portada',
            'nombre' => 'Página 1'
        ]);
    }

    /**
     * Test: Aplicar plantilla a página con frames existentes elimina los anteriores y crea los nuevos
     * Validates: Req 1.4
     */
    public function test_aplicar_plantilla_elimina_elementos_anteriores_y_crea_nuevos(): void
    {
        // Crear elementos previos en la página
        PeriodicoElemento::create([
            'pagina_id' => $this->pagina->id,
            'frame_id' => 'f-prev-1',
            'tipo' => 'headline',
            'x' => 10,
            'y' => 10,
            'ancho' => 200,
            'alto' => 50,
            'orden' => 1,
            'contenido' => 'Elemento previo a eliminar'
        ]);
        $this->assertEquals(1, $this->pagina->elementos()->count());

        $plantilla = PeriodicoPlantilla::where('slug', 'politica-a')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/periodico/templates/{$plantilla->id}/apply/{$this->pagina->id}", [
                'confirm' => true
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verificar que el elemento anterior fue eliminado y se crearon los de la plantilla
        $this->assertDatabaseMissing('periodico_elementos', [
            'pagina_id' => $this->pagina->id,
            'contenido' => 'Elemento previo a eliminar'
        ]);

        $frameCount = count($plantilla->frames);
        $this->assertEquals($frameCount, $this->pagina->elementos()->count());
    }

    /**
     * Test: Aplicar plantilla con ID inexistente usa fallback 'portada-default'
     * Validates: Req 1.7
     */
    public function test_aplicar_plantilla_inexistente_usa_fallback_portada_default(): void
    {
        $inexistentId = 'id-totalmente-inexistente-9999';

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/periodico/templates/{$inexistentId}/apply/{$this->pagina->id}", [
                'confirm' => true
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $portadaDefault = PeriodicoPlantilla::where('slug', 'portada-default')->firstOrFail();
        $this->pagina->refresh();

        $this->assertEquals($portadaDefault->id, $this->pagina->plantilla_id);
        $this->assertEquals(count($portadaDefault->frames), $this->pagina->elementos()->count());
    }
}
