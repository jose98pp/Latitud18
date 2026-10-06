<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPagina;
use App\Models\PeriodicoElemento;
use App\Models\Noticia;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NoticiaAsignacionPropertyTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Property 7: Round-trip de asignación de noticia_id y retención de contenido al desvincular
     * Validates: Requirements 4.3, 4.6
     */
    public function test_property_7_round_trip_asignacion_y_desvinculacion_de_noticia(): void
    {
        $category = Category::firstOrCreate(
            ['name' => 'País'],
            ['descripcion' => 'Noticias nacionales']
        );

        $noticia = Noticia::create([
            'titulo' => 'Noticia Asignada Test',
            'subtitulo' => 'Subtítulo Asignado',
            'contenido' => 'Cuerpo completo de la noticia asignada',
            'autor' => 'Autor Asignado',
            'category_id' => $category->id,
            'publicada' => true,
        ]);

        $edicion = PeriodicoEdicion::create([
            'id' => 'ed-prop7-' . uniqid(),
            'numero_edicion' => 'Ed P7 Test',
            'fecha' => '5 de Octubre de 2026',
            'titulo' => 'Edición Prop 7',
            'num_paginas' => 1,
            'estado' => 'borrador'
        ]);

        $pagina = PeriodicoPagina::create([
            'edicion_id' => $edicion->id,
            'numero' => 1,
            'seccion' => 'País',
            'nombre' => 'Página 1'
        ]);

        // 1. Asignar noticia al frame de tipo article
        $elemento = PeriodicoElemento::create([
            'pagina_id' => $pagina->id,
            'frame_id' => 'f-art-p7',
            'tipo' => 'article',
            'x' => 10,
            'y' => 10,
            'w' => 400,
            'h' => 300,
            'contenido' => $noticia->contenido,
            'propiedades' => [
                'noticia_id' => $noticia->id,
                'titular' => $noticia->titulo,
                'subtitulo' => $noticia->subtitulo,
                'cuerpo' => $noticia->contenido,
                'autor' => $noticia->autor,
                'categoria_label' => $category->name,
                'categoria_color' => $category->color,
            ]
        ]);

        $elemento->refresh();

        // Validar Req 4.3: noticia_id almacenado coincide exactamente con la noticia
        $this->assertEquals($noticia->id, $elemento->propiedades['noticia_id']);
        $this->assertEquals($noticia->titulo, $elemento->propiedades['titular']);
        $this->assertEquals($noticia->subtitulo, $elemento->propiedades['subtitulo']);
        $this->assertEquals($noticia->contenido, $elemento->propiedades['cuerpo']);
        $this->assertEquals($noticia->autor, $elemento->propiedades['autor']);
        $this->assertEquals($category->name, $elemento->propiedades['categoria_label']);

        // 2. Operación de desvinculación (detach)
        // Borra noticia_id pero retiene titular, subtitulo, cuerpo, autor, categoria_label (Req 4.6)
        $props = $elemento->propiedades;
        $props['noticia_id'] = null;

        $elemento->update(['propiedades' => $props]);
        $elemento->refresh();

        $this->assertNull($elemento->propiedades['noticia_id']);
        $this->assertEquals($noticia->titulo, $elemento->propiedades['titular']);
        $this->assertEquals($noticia->subtitulo, $elemento->propiedades['subtitulo']);
        $this->assertEquals($noticia->contenido, $elemento->propiedades['cuerpo']);
        $this->assertEquals($noticia->autor, $elemento->propiedades['autor']);
        $this->assertEquals($category->name, $elemento->propiedades['categoria_label']);
    }
}
