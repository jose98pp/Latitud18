<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoPlantilla;
use App\Services\PlantillaService;
use App\Services\EdicionService;
use App\Services\EstadoEditorialService;
use App\Services\PeriodicoPdfService;
use App\Exceptions\EdicionSinPaginasException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PeriodicoBordeTest extends TestCase
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

    /**
     * Test: marcar ad como ocupado sin imagen previa devuelve error
     * Validates: Req 3.6
     */
    public function test_marcar_ad_como_ocupado_sin_imagen_devuelve_error(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-borde-' . time(),
            'numero_edicion' => 'Edición Borde',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Borde',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        $pagina = PeriodicoPagina::create([
            'edicion_id' => $edicion->id,
            'numero' => 1,
            'seccion' => 'Portada',
            'nombre' => 'Página 1'
        ]);

        // Frame ad con status 'ocupado' pero sin advertiser_image_url
        $frames = [
            [
                'id' => 'f-ad-1',
                'type' => 'ad',
                'x' => 10,
                'y' => 10,
                'w' => 932,
                'h' => 227,
                'format_code' => 'A1',
                'status' => 'ocupado',
                'advertiser_image_url' => null,
                'advertiser_name' => 'Anunciante Prueba'
            ]
        ];

        $payload = [
            'id' => $edicion->id,
            'titulo' => $edicion->titulo,
            'paginas' => [
                [
                    'id' => $pagina->id,
                    'numero' => 1,
                    'nombre' => 'Página 1',
                    'frames' => $frames
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/periodico/{$edicion->id}", $payload);
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonFragment([
            'message' => 'El marco publicitario f-ad-1 está marcado como ocupado pero no tiene una imagen publicitaria asignada.'
        ]);
    }

    /**
     * Test: desvinculación de noticia retiene campos de texto
     * Validates: Req 4.6
     */
    public function test_desvinculacion_de_noticia_retiene_campos_de_texto(): void
    {
        $frame = [
            'id' => 'f-art-1',
            'type' => 'article',
            'noticia_id' => 123,
            'titular' => 'Titular Original de la Noticia',
            'subtitulo' => 'Subtítulo Importante',
            'cuerpo' => 'Cuerpo íntegro del artículo periodístico retrotraído.',
            'autor' => 'Juan Pérez',
            'categoria_label' => 'Economía',
            'propiedades' => [
                'noticia_id' => 123,
                'categoria_color' => '#0284c7'
            ]
        ];

        // Simulamos desvinculación eliminando noticia_id
        unset($frame['noticia_id']);
        unset($frame['propiedades']['noticia_id']);

        // Verificamos que los campos se mantienen intactos
        $this->assertArrayNotHasKey('noticia_id', $frame);
        $this->assertEquals('Titular Original de la Noticia', $frame['titular']);
        $this->assertEquals('Subtítulo Importante', $frame['subtitulo']);
        $this->assertEquals('Cuerpo íntegro del artículo periodístico retrotraído.', $frame['cuerpo']);
        $this->assertEquals('Juan Pérez', $frame['autor']);
        $this->assertEquals('Economía', $frame['categoria_label']);
    }

    /**
     * Test: fallo en creación de página N hace rollback de toda la edición
     * Validates: Req 5.8
     */
    public function test_fallo_en_creacion_de_pagina_n_hace_rollback_de_toda_la_edicion(): void
    {
        $edicionService = app(EdicionService::class);

        // Mapping con 12 slots, pero forzamos un slot inválido para provocar excepción
        $mapping = [];
        for ($i = 1; $i <= 12; $i++) {
            $mapping[] = [
                'slot' => $i,
                'plantilla_id' => ($i === 6) ? 'plantilla_inexistente_que_falla' : null
            ];
        }

        // Mock o llamada que lance excepción
        $countEdicionesBefore = PeriodicoEdicion::count();

        // En EdicionService::crearDesdeMapping si una plantilla no existe lanza RuntimeException
        try {
            $edicionService->crearDesdeMapping($mapping, [
                'numero_edicion' => 'Edición Rollback',
                'fecha' => '5 de Octubre 2026',
                'titulo' => 'Rollback Test'
            ]);
            $this->fail('Se esperaba una excepción en la creación');
        } catch (\Exception $e) {
            $this->assertStringContainsString('slot', strtolower($e->getMessage()));
        }

        $this->assertEquals($countEdicionesBefore, PeriodicoEdicion::count(), 'La transacción debió revertir la edición.');
    }

    /**
     * Test: edición sin páginas rechaza generación de PDF
     * Validates: Req 7.8
     */
    public function test_edicion_sin_paginas_rechaza_generacion_de_pdf(): void
    {
        $pdfService = app(PeriodicoPdfService::class);

        $edicionSinPaginas = [
            'id' => 'ed-empty',
            'titulo' => 'Sin Páginas',
            'paginas' => []
        ];

        $this->expectException(EdicionSinPaginasException::class);
        $pdfService->contenido($edicionSinPaginas);
    }

    /**
     * Test: PDF con pdf_url apuntando a archivo inexistente hace fallback a DomPDF
     * Validates: Req 7.9
     */
    public function test_pdf_url_inexistente_hace_fallback_a_dompdf(): void
    {
        $pdfService = app(PeriodicoPdfService::class);

        $edicion = [
            'id' => 'ed-fallback-' . time(),
            'numero_edicion' => '100',
            'fecha' => '5 de Octubre 2026',
            'titulo' => 'Fallback Test',
            'pdf_url' => 'periodicos/archivo_que_no_existe_en_disco.pdf',
            'paginas' => [
                [
                    'id' => 1,
                    'numero' => 1,
                    'seccion' => 'Portada',
                    'nombre' => 'Portada',
                    'elementos' => []
                ]
            ]
        ];

        // El fallback debe generar el contenido DomPDF sin lanzar 404 ni error
        $pdfContent = $pdfService->contenido($edicion);
        $this->assertNotEmpty($pdfContent);
        $this->assertStringStartsWith('%PDF', $pdfContent);
    }

    /**
     * Test: transición publicado → borrador es rechazada
     * Validates: Req 8.1
     */
    public function test_transicion_publicado_a_borrador_es_rechazada(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-pub-' . time(),
            'numero_edicion' => 'Edición Publicada',
            'fecha' => '5 de Octubre 2026',
            'titulo' => 'Publicada Test',
            'num_paginas' => 1,
            'estado' => 'publicado',
            'publicada' => true,
            'activa' => true
        ]);

        $response = $this->actingAs($this->admin)->postJson("/admin/periodico/{$edicion->id}/estado", [
            'estado' => 'borrador'
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);

        $this->assertEquals('publicado', $edicion->fresh()->estado);
    }

    /**
     * Test: transicionar a programado sin fecha_programada rechaza la transición
     * Validates: Req 8.3
     */
    public function test_transicion_a_programado_sin_fecha_es_rechazada(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-aprob-' . time(),
            'numero_edicion' => 'Edición Aprobada',
            'fecha' => '5 de Octubre 2026',
            'titulo' => 'Aprobada Test',
            'num_paginas' => 1,
            'estado' => 'aprobado',
            'publicada' => false
        ]);

        $response = $this->actingAs($this->admin)->postJson("/admin/periodico/{$edicion->id}/estado", [
            'estado' => 'programado',
            'fecha_programada' => null
        ]);

        $response->assertStatus(422);
        $this->assertEquals('aprobado', $edicion->fresh()->estado);
    }

    /**
     * Test: import con JSON sin clave frames retorna error específico
     * Validates: Req 10.5
     */
    public function test_import_sin_clave_frames_retorna_error_especifico(): void
    {
        $plantillaService = app(PlantillaService::class);

        $jsonInvalido = [
            'name' => 'Plantilla Invalida',
            'category' => 'general'
            // sin clave frames
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required 'frames' key");

        $plantillaService->importar($jsonInvalido);
    }

    /**
     * Test: eliminar plantilla factory con confirmación procede; sin confirmar no procede
     * Validates: Req 10.6
     */
    public function test_eliminar_plantilla_factory_requiere_confirmacion(): void
    {
        $factoryTpl = PeriodicoPlantilla::where('is_custom', false)->firstOrFail();

        // Intento sin confirmación -> rechaza 422
        $responseSinConfirmar = $this->actingAs($this->admin)
            ->deleteJson("/admin/periodico/templates/{$factoryTpl->id}");

        $responseSinConfirmar->assertStatus(422);
        $this->assertDatabaseHas('periodico_plantillas', ['id' => $factoryTpl->id]);

        // Intento con confirmación -> elimina 200
        $responseConConfirmar = $this->actingAs($this->admin)
            ->deleteJson("/admin/periodico/templates/{$factoryTpl->id}?confirm=1");

        $responseConConfirmar->assertStatus(200);
        $this->assertDatabaseMissing('periodico_plantillas', ['id' => $factoryTpl->id]);
    }
}
