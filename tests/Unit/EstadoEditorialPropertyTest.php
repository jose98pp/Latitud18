<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PeriodicoEdicion;
use App\Services\EstadoEditorialService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EstadoEditorialPropertyTest extends TestCase
{
    use DatabaseTransactions;

    protected EstadoEditorialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EstadoEditorialService();
    }

    /**
     * Property 12: Máquina de estados editorial
     * Validates: Requirements 8.1
     */
    public function test_property_12_maquina_de_estados_editorial_transiciones_permitidas_e_invalidas(): void
    {
        $grafo = [
            'borrador'   => ['revision'],
            'revision'   => ['aprobado', 'borrador'],
            'aprobado'   => ['programado', 'revision'],
            'programado' => ['publicado', 'aprobado'],
            'publicado'  => [],
        ];

        $todosLosEstados = array_keys($grafo);

        foreach ($todosLosEstados as $actual) {
            foreach ($todosLosEstados as $destino) {
                $permitido = in_array($destino, $grafo[$actual], true);

                $this->assertEquals(
                    $permitido,
                    $this->service->puedeTransicionar($actual, $destino),
                    "Falla de validación en puedeTransicionar de '{$actual}' a '{$destino}'"
                );

                if (!$permitido) {
                    $edicion = PeriodicoEdicion::create([
                        'id' => 'ed-test-p12-' . uniqid(),
                        'numero_edicion' => 'Ed P12 Test',
                        'fecha' => '5 de Octubre de 2026',
                        'titulo' => 'Test Transición',
                        'num_paginas' => 1,
                        'estado' => $actual,
                    ]);

                    try {
                        $this->service->transicionar($edicion, $destino);
                        $this->fail("Debió lanzar DomainException para transición {$actual}→{$destino}");
                    } catch (\DomainException $e) {
                        $this->assertStringContainsString("Transición {$actual}→{$destino} no permitida", $e->getMessage());
                    }

                    $edicion->refresh();
                    $this->assertEquals($actual, $edicion->estado, "El estado de la edición no debe alterarse tras transición fallida");
                }
            }
        }
    }

    /**
     * Property 13: Exclusividad de edición activa al publicar
     * Validates: Requirements 8.2
     */
    public function test_property_13_exclusividad_de_edicion_activa_al_publicar(): void
    {
        // Crear 3 ediciones
        $ed1 = PeriodicoEdicion::create([
            'id' => 'ed-p13-1-' . uniqid(),
            'numero_edicion' => 'Ed 1',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición 1',
            'num_paginas' => 1,
            'estado' => 'publicado',
            'activa' => true,
            'publicada' => true,
        ]);

        $ed2 = PeriodicoEdicion::create([
            'id' => 'ed-p13-2-' . uniqid(),
            'numero_edicion' => 'Ed 2',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición 2',
            'num_paginas' => 1,
            'estado' => 'programado',
            'activa' => false,
            'publicada' => false,
        ]);

        $ed3 = PeriodicoEdicion::create([
            'id' => 'ed-p13-3-' . uniqid(),
            'numero_edicion' => 'Ed 3',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición 3',
            'num_paginas' => 1,
            'estado' => 'borrador',
            'activa' => false,
            'publicada' => false,
        ]);

        // Publicar ed2 (transición permitida de programado -> publicado)
        $this->service->transicionar($ed2, 'publicado');

        $ed1->refresh();
        $ed2->refresh();
        $ed3->refresh();

        $this->assertTrue((bool)$ed2->activa, 'La edición publicada debe estar activa');
        $this->assertTrue((bool)$ed2->publicada, 'La edición publicada debe tener publicada=true');
        $this->assertNotNull($ed2->fecha_publicacion, 'Debe tener fecha_publicacion');

        $this->assertFalse((bool)$ed1->activa, 'La edición 1 previa debe desactivarse');
        $this->assertFalse((bool)$ed3->activa, 'La edición 3 debe permanecer inactiva');

        // Confirmar que exactamente 1 edición en toda la BD tiene activa = true
        $activas = PeriodicoEdicion::where('activa', true)->count();
        $this->assertEquals(1, $activas);
    }

    /**
     * Property 14: Validación de fecha programada
     * Validates: Requirements 8.3
     */
    public function test_property_14_validacion_de_fecha_programada(): void
    {
        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-p14-' . uniqid(),
            'numero_edicion' => 'Ed P14',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición P14',
            'num_paginas' => 1,
            'estado' => 'aprobado',
        ]);

        // 1. Sin fecha_programada
        try {
            $this->service->transicionar($edicion, 'programado', []);
            $this->fail("Debió fallar por fecha_programada faltante");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("Se requiere 'fecha_programada'", $e->getMessage());
        }
        $edicion->refresh();
        $this->assertEquals('aprobado', $edicion->estado);

        // 2. Con fecha pasada
        try {
            $this->service->transicionar($edicion, 'programado', [
                'fecha_programada' => now()->subDay()->toDateTimeString()
            ]);
            $this->fail("Debió fallar por fecha_programada en el pasado");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("debe ser al menos 1 minuto en el futuro", $e->getMessage());
        }
        $edicion->refresh();
        $this->assertEquals('aprobado', $edicion->estado);

        // 3. Con fecha menor a 1 minuto en el futuro (ej. 30 segundos)
        try {
            $this->service->transicionar($edicion, 'programado', [
                'fecha_programada' => now()->addSeconds(30)->toDateTimeString()
            ]);
            $this->fail("Debió fallar por fecha_programada < 1 minuto");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("debe ser al menos 1 minuto en el futuro", $e->getMessage());
        }
        $edicion->refresh();
        $this->assertEquals('aprobado', $edicion->estado);

        // 4. Con fecha válida (1 hora en el futuro)
        $fechaValida = now()->addHour();
        $this->service->transicionar($edicion, 'programado', [
            'fecha_programada' => $fechaValida->toDateTimeString()
        ]);
        $edicion->refresh();
        $this->assertEquals('programado', $edicion->estado);
        $this->assertNotNull($edicion->fecha_programada);
    }
}
