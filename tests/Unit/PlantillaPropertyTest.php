<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoPlantilla;
use App\Services\PlantillaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlantillaPropertyTest extends TestCase
{
    use DatabaseTransactions;

    protected PlantillaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlantillaService();
    }

    /**
     * Property 1: Deep copy fiel de frames al aplicar plantilla
     * Validates: Requirements 1.2
     */
    public function test_property_1_deep_copy_de_frames_al_aplicar_plantilla(): void
    {
        $framesOriginales = [
            [
                'id' => 'frame-art-1',
                'type' => 'article',
                'x' => 12,
                'y' => 100,
                'w' => 450,
                'h' => 300,
                'content' => 'Contenido de prueba',
                'propiedades' => ['titular' => 'Titular Original', 'autor' => 'Redacción']
            ],
            [
                'id' => 'frame-ad-1',
                'type' => 'ad',
                'x' => 470,
                'y' => 100,
                'w' => 242,
                'h' => 492,
                'content' => '',
                'propiedades' => ['format_code' => 'D1', 'status' => 'disponible']
            ]
        ];

        $plantilla = PeriodicoPlantilla::create([
            'id' => 'tpl-prop1-' . uniqid(),
            'nombre' => 'Plantilla Prop 1',
            'slug' => 'plantilla-prop-1-' . uniqid(),
            'categoria' => 'politica',
            'descripcion' => 'Descripción prueba',
            'preview_color' => '#0B1F3A',
            'is_custom' => true,
            'frames' => $framesOriginales
        ]);

        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-prop1-' . uniqid(),
            'numero_edicion' => 'Edición Prop 1',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Prop 1',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        $pagina = PeriodicoPagina::create([
            'edicion_id' => $edicion->id,
            'numero' => 1,
            'seccion' => 'General',
            'nombre' => 'Página 1'
        ]);

        $this->service->aplicarAPagina($plantilla, $pagina);

        $pagina->refresh();
        $elementos = $pagina->elementos;

        $this->assertCount(2, $elementos);
        $this->assertEquals($plantilla->id, $pagina->plantilla_id);

        // Verificar que los elementos tienen los mismos datos
        $elem1 = $elementos[0];
        $this->assertEquals('article', $elem1->tipo);
        $this->assertEquals(12, $elem1->x);
        $this->assertEquals(100, $elem1->y);
        $this->assertEquals(450, $elem1->w);
        $this->assertEquals(300, $elem1->h);
        $this->assertEquals('Contenido de prueba', $elem1->contenido);
        $this->assertEquals('Titular Original', $elem1->propiedades['titular']);

        $elem2 = $elementos[1];
        $this->assertEquals('ad', $elem2->tipo);
        $this->assertEquals(470, $elem2->x);
        $this->assertEquals('D1', $elem2->propiedades['format_code']);

        // Verificar que la plantilla original no fue alterada
        $plantilla->refresh();
        $this->assertEquals($framesOriginales, $plantilla->frames);
    }

    /**
     * Property 2: Validez y unicidad del slug generado
     * Validates: Requirements 1.6
     */
    public function test_property_2_validez_y_unicidad_del_slug(): void
    {
        $nombre = '¡Nueva Plantilla Portada 2026! @#$';
        $slug1 = $this->service->generarSlugUnico($nombre);

        // (a) alfanumérico en minúsculas y guiones
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug1);
        // (b) no inicia ni termina con guión
        $this->assertStringStartsNotWith('-', $slug1);
        $this->assertStringEndsNotWith('-', $slug1);

        // Crear una plantilla con ese slug
        PeriodicoPlantilla::create([
            'id' => 'tpl-slug-test-' . uniqid(),
            'nombre' => $nombre,
            'slug' => $slug1,
            'categoria' => 'portada',
            'is_custom' => true,
            'frames' => [['id' => 'f1', 'type' => 'header', 'x' => 0, 'y' => 0, 'w' => 720, 'h' => 100]]
        ]);

        // (c) Si ya existe, debe generar con sufijo numérico único
        $slug2 = $this->service->generarSlugUnico($nombre);
        $this->assertEquals($slug1 . '-2', $slug2);

        PeriodicoPlantilla::create([
            'id' => 'tpl-slug-test2-' . uniqid(),
            'nombre' => $nombre,
            'slug' => $slug2,
            'categoria' => 'portada',
            'is_custom' => true,
            'frames' => [['id' => 'f1', 'type' => 'header', 'x' => 0, 'y' => 0, 'w' => 720, 'h' => 100]]
        ]);

        $slug3 = $this->service->generarSlugUnico($nombre);
        $this->assertEquals($slug1 . '-3', $slug3);
    }

    /**
     * Property 3: Persistencia válida de plantilla custom
     * Validates: Requirements 1.5
     */
    public function test_property_3_persistencia_valida_de_plantilla_custom(): void
    {
        $frames = [
            ['id' => 'f-custom-1', 'type' => 'article', 'x' => 10, 'y' => 20, 'w' => 300, 'h' => 400]
        ];

        $plantilla = PeriodicoPlantilla::create([
            'id' => 'tpl-cust-' . uniqid(),
            'nombre' => 'Plantilla Custom Test',
            'slug' => 'plantilla-custom-test-' . uniqid(),
            'categoria' => 'economia',
            'descripcion' => 'Descripción de prueba custom',
            'preview_color' => '#D71920',
            'is_custom' => true,
            'frames' => $frames
        ]);

        $this->assertTrue($plantilla->is_custom);
        $this->assertEquals('Plantilla Custom Test', $plantilla->nombre);
        $this->assertEquals('economia', $plantilla->categoria);
        $this->assertEquals('Descripción de prueba custom', $plantilla->descripcion);
        $this->assertEquals('#D71920', $plantilla->preview_color);
        $this->assertEquals($frames, $plantilla->frames);
    }

    /**
     * Property 15: Deep copy independiente al duplicar plantilla
     * Validates: Requirements 10.2
     */
    public function test_property_15_deep_copy_independiente_al_duplicar_plantilla(): void
    {
        $framesOriginales = [
            ['id' => 'f-orig-1', 'type' => 'article', 'x' => 10, 'y' => 20, 'w' => 300, 'h' => 400, 'content' => 'Original']
        ];

        $original = PeriodicoPlantilla::create([
            'id' => 'tpl-orig-' . uniqid(),
            'nombre' => 'Plantilla Original',
            'slug' => 'plantilla-original-' . uniqid(),
            'categoria' => 'pais',
            'descripcion' => 'Desc Original',
            'preview_color' => '#1A202C',
            'is_custom' => false,
            'frames' => $framesOriginales
        ]);

        $copia = $this->service->duplicar($original);

        $this->assertTrue($copia->is_custom);
        $this->assertEquals('Plantilla Original (Copia)', $copia->nombre);
        $this->assertNotEquals($original->id, $copia->id);
        $this->assertEquals($framesOriginales, $copia->frames);

        // Modificar los frames de la copia
        $framesModificados = [
            ['id' => 'f-copia-mod', 'type' => 'ad', 'x' => 50, 'y' => 50, 'w' => 200, 'h' => 200, 'content' => 'Modificado']
        ];
        $copia->update(['frames' => $framesModificados]);

        // Verificar que la original permanece intacta
        $original->refresh();
        $this->assertEquals($framesOriginales, $original->frames);
    }

    /**
     * Property 16: Exportación con campos requeridos
     * Validates: Requirements 10.3
     */
    public function test_property_16_exportacion_con_campos_requeridos(): void
    {
        $plantilla = PeriodicoPlantilla::create([
            'id' => 'tpl-exp-' . uniqid(),
            'nombre' => 'Plantilla Exportable',
            'slug' => 'plantilla-exportable-' . uniqid(),
            'categoria' => 'santa-cruz',
            'descripcion' => 'Para exportación',
            'preview_color' => '#0B1F3A',
            'is_custom' => true,
            'frames' => [
                ['id' => 'f1', 'type' => 'header', 'x' => 0, 'y' => 0, 'w' => 720, 'h' => 100]
            ]
        ]);

        $export = $this->service->exportar($plantilla);

        $this->assertArrayHasKey('id', $export);
        $this->assertArrayHasKey('name', $export);
        $this->assertArrayHasKey('category', $export);
        $this->assertArrayHasKey('description', $export);
        $this->assertArrayHasKey('preview_color', $export);
        $this->assertArrayHasKey('frames', $export);

        $this->assertEquals($plantilla->id, $export['id']);
        $this->assertEquals('Plantilla Exportable', $export['name']);
        $this->assertEquals('santa-cruz', $export['category']);
        $this->assertIsArray($export['frames']);
        $this->assertNotEmpty($export['frames']);
    }

    /**
     * Property 17: Validación de importación de plantilla
     * Validates: Requirements 10.4, 10.5
     */
    public function test_property_17_validacion_de_importacion_de_plantilla(): void
    {
        // Caso exitoso
        $payloadValido = [
            'name' => 'Plantilla Importada Test',
            'category' => 'seguridad',
            'description' => 'Importada correctamente',
            'preview_color' => '#D71920',
            'frames' => [
                ['id' => 'f-imp-1', 'type' => 'article', 'x' => 0, 'y' => 0, 'w' => 400, 'h' => 300]
            ]
        ];

        $importada = $this->service->importar($payloadValido);
        $this->assertInstanceOf(PeriodicoPlantilla::class, $importada);
        $this->assertTrue($importada->is_custom);
        $this->assertEquals('Plantilla Importada Test', $importada->nombre);

        // (c) Falta clave 'frames'
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required 'frames' key");
        $this->service->importar([
            'name' => 'Invalida Sin Frames',
            'category' => 'general'
        ]);
    }

    /**
     * Property 17 (bis): Frames vacíos lanzan excepción
     */
    public function test_property_17_frames_vacio_falla(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'frames' array must not be empty");
        $this->service->importar([
            'name' => 'Invalida Frames Vacios',
            'category' => 'general',
            'frames' => []
        ]);
    }
}
