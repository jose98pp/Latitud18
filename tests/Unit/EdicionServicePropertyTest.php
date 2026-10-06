<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use App\Services\EdicionService;
use App\Services\PlantillaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EdicionServicePropertyTest extends TestCase
{
    use DatabaseTransactions;

    protected EdicionService $edicionService;
    protected PlantillaService $plantillaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plantillaService = new PlantillaService();
        $this->edicionService = new EdicionService($this->plantillaService);
    }

    /**
     * Property 8: Invariantes de la edición de 12 páginas
     * Validates: Requirements 5.1, 5.2, 5.3
     */
    public function test_property_8_invariantes_de_la_edicion_de_12_paginas(): void
    {
        $portadaTpl = PeriodicoPlantilla::where('slug', 'portada-default')->first()
            ?: PeriodicoPlantilla::first();

        // Configuración mixta: slot 1 con plantilla, otros con null o plantillas
        $mapping = [];
        for ($s = 1; $s <= 12; $s++) {
            $mapping[] = [
                'slot' => $s,
                'plantilla_id' => ($s === 1 && $portadaTpl) ? $portadaTpl->id : null,
            ];
        }

        $atributos = [
            'numero_edicion' => 'Edición P8 Test',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición 12P Propiedad 8',
        ];

        $edicion = $this->edicionService->crearDesdeMapping($mapping, $atributos);

        // Invariantes
        $this->assertInstanceOf(PeriodicoEdicion::class, $edicion);
        $this->assertEquals(12, $edicion->num_paginas);
        $this->assertEquals('borrador', $edicion->estado);
        $this->assertFalse((bool)$edicion->publicada);
        $this->assertFalse((bool)$edicion->activa);

        $paginas = $edicion->paginas()->orderBy('numero', 'asc')->get();
        $this->assertCount(12, $paginas);

        $seccionesEsperadas = [
            1  => 'Portada',
            2  => 'Editorial/Opinión',
            3  => 'Política',
            4  => 'Política',
            5  => 'Santa Cruz',
            6  => 'Santa Cruz',
            7  => 'País',
            8  => 'País',
            9  => 'Economía',
            10 => 'Economía',
            11 => 'Seguridad/Judicial',
            12 => 'Mundo/Deportes/Cultura',
        ];

        foreach ($paginas as $index => $pagina) {
            $slotEsperado = $index + 1;
            $this->assertEquals($slotEsperado, $pagina->numero);
            $this->assertEquals($seccionesEsperadas[$slotEsperado], $pagina->seccion);
        }
    }

    /**
     * Property 9: Serialización del mapping de plantilla de edición
     * Validates: Requirements 5.5
     */
    public function test_property_9_serializacion_del_mapping_de_plantilla_de_edicion(): void
    {
        $inputMapping = [];
        for ($slot = 1; $slot <= 12; $slot++) {
            $inputMapping[] = [
                'slot' => $slot,
                'plantilla_id' => ($slot % 2 === 0) ? 'tpl-dummy-' . $slot : null,
            ];
        }

        $plantillaEdicion = PeriodicoPlantillaEdicion::create([
            'nombre' => 'Plantilla Edición P9 Test',
            'descripcion' => 'Prueba de serialización de mapping de 12 slots',
            'mapping' => $inputMapping,
        ]);

        $plantillaEdicion->refresh();

        $this->assertIsArray($plantillaEdicion->mapping);
        $this->assertCount(12, $plantillaEdicion->mapping);

        $slotsEncontrados = [];
        foreach ($plantillaEdicion->mapping as $idx => $entry) {
            $this->assertArrayHasKey('slot', $entry);
            $this->assertArrayHasKey('plantilla_id', $entry);

            $slotNum = (int)$entry['slot'];
            $this->assertGreaterThanOrEqual(1, $slotNum);
            $this->assertLessThanOrEqual(12, $slotNum);
            $this->assertNotContains($slotNum, $slotsEncontrados);

            $slotsEncontrados[] = $slotNum;

            // Comparar con el input original
            $this->assertEquals($inputMapping[$idx]['slot'], $entry['slot']);
            $this->assertEquals($inputMapping[$idx]['plantilla_id'], $entry['plantilla_id']);
        }

        $this->assertCount(12, $slotsEncontrados);
    }
}
