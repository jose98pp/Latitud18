<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PeriodicoHttpTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@latitud18.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('secret123'), 'role' => 'admin']
        );
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            @ob_end_clean();
        }
        parent::tearDown();
    }

    /**
     * Test: GET /admin/periodico carga la vista con ediciones y plantillas
     */
    public function test_get_admin_periodico_carga_vista_con_ediciones_y_plantillas(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/periodico');

        $response->assertStatus(200);
        $response->assertViewIs('admin.periodico.index');
        $response->assertViewHas(['ediciones', 'currentEdicion', 'plantillasModels', 'categorias']);
    }

    /**
     * Test: PUT /admin/periodico/{id} actualiza frames y devuelve edición actualizada
     */
    public function test_put_admin_periodico_actualiza_frames_y_devuelve_edicion(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-http-' . time(),
            'numero_edicion' => 'Edición HTTP',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición HTTP Test',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        $pagina = PeriodicoPagina::create([
            'edicion_id' => $edicion->id,
            'numero' => 1,
            'seccion' => 'Portada',
            'nombre' => 'Página 1'
        ]);

        $payload = [
            'id' => $edicion->id,
            'titulo' => 'Título Actualizado HTTP',
            'paginas' => [
                [
                    'id' => $pagina->id,
                    'numero' => 1,
                    'nombre' => 'Página 1 Renovada',
                    'frames' => [
                        [
                            'id' => 'f-text-http',
                            'type' => 'headline',
                            'x' => 50,
                            'y' => 60,
                            'w' => 600,
                            'h' => 80,
                            'content' => 'Titular Actualizado Vía HTTP'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/periodico/{$edicion->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('edicion.titulo', 'Título Actualizado HTTP');

        $this->assertDatabaseHas('periodico_ediciones', [
            'id' => $edicion->id,
            'titulo' => 'Título Actualizado HTTP'
        ]);
    }

    /**
     * Test: POST /admin/periodico/{id}/estado rechaza transición inválida con HTTP 422
     */
    public function test_post_admin_periodico_estado_rechaza_transicion_invalida_con_422(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-state-inv-' . time(),
            'numero_edicion' => 'Edición Test Inv',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Test Inv',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        // Intentar pasar directamente de borrador a publicado sin pasar por revision/aprobado
        $response = $this->actingAs($this->admin)->postJson("/admin/periodico/{$edicion->id}/estado", [
            'estado' => 'publicado'
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /**
     * Test: POST /admin/periodico/plantilla-edicion/{id}/crear-edicion crea 12 páginas
     */
    public function test_post_crear_edicion_desde_plantilla_edicion_crea_12_paginas(): void
    {
        $portada = PeriodicoPlantilla::where('slug', 'portada-default')->firstOrFail();

        $mapping = [];
        for ($slot = 1; $slot <= 12; $slot++) {
            $mapping[] = [
                'slot' => $slot,
                'plantilla_id' => ($slot === 1) ? $portada->id : null
            ];
        }

        $plantillaEdicion = PeriodicoPlantillaEdicion::create([
            'nombre' => 'Esquema Test HTTP 12P',
            'descripcion' => 'Esquema de prueba',
            'mapping' => $mapping
        ]);

        $response = $this->actingAs($this->admin)->postJson("/admin/periodico/plantilla-edicion/{$plantillaEdicion->id}/crear-edicion", [
            'numero_edicion' => 'Edición 12P ' . time(),
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Completa 12 Páginas HTTP'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['edicion', 'redirect_url']);

        $edicionData = $response->json('edicion');
        $this->assertEquals(12, $edicionData['num_paginas']);
        $this->assertCount(12, $edicionData['paginas']);
    }

    /**
     * Test: GET /admin/periodico/{id}/pdf devuelve PDF con headers correctos
     */
    public function test_get_admin_periodico_pdf_devuelve_pdf_con_headers_correctos(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-pdf-hdr-' . time(),
            'numero_edicion' => 'Edición PDF',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición PDF Header Test',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        PeriodicoPagina::create([
            'edicion_id' => $edicion->id,
            'numero' => 1,
            'seccion' => 'Portada',
            'nombre' => 'Página 1'
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/periodico/{$edicion->id}/pdf");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString("filename=latitud-18-ed-{$edicion->id}.pdf", $response->headers->get('Content-Disposition'));
    }

    /**
     * Test: GET /periodico/ solo muestra ediciones con estado = publicado
     */
    public function test_get_periodico_publico_solo_muestra_ediciones_publicadas(): void
    {
        // Desactivar cualquier edición previa para asegurar que la nueva activa sea la única
        PeriodicoEdicion::where('activa', true)->update(['activa' => false]);

        $edicionBorrador = PeriodicoEdicion::create([
            'id' => 'ed-borr-pub-' . time(),
            'numero_edicion' => 'Borrador Oculto',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Borrador Oculto Público',
            'num_paginas' => 1,
            'estado' => 'borrador',
            'publicada' => false,
            'activa' => false
        ]);

        $edicionPublicada = PeriodicoEdicion::create([
            'id' => 'ed-publ-vis-' . time(),
            'numero_edicion' => 'Publicada Visible',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Publicada Visible Público',
            'num_paginas' => 1,
            'estado' => 'publicado',
            'publicada' => true,
            'activa' => true
        ]);

        $response = $this->get('/periodico');
        $response->assertStatus(200);
        $response->assertSee('Publicada Visible Público');
        $response->assertDontSee('Borrador Oculto Público');
    }
}
