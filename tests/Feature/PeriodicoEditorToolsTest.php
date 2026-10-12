<?php

namespace Tests\Feature;

use App\Models\PeriodicoElemento;
use App\Models\PeriodicoPlantilla;
use App\Models\User;
use App\Services\PeriodicoPdfService;
use Database\Seeders\Latitud18ReferenciaSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PeriodicoEditorToolsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Latitud18ReferenciaSeeder::class);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_delete_and_reorder_pages_are_preserved_after_reload(): void
    {
        $edition = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        for ($i = 2; $i <= 3; $i++) {
            $page = $edition->paginas()->create(['numero'=>$i,'nombre'=>'Página '.$i,'ancho'=>720,'alto'=>1106]);
            $page->crearElementoDesdeFrame(['id'=>'p'.$i,'type'=>'text','content'=>'Contenido '.$i]);
        }
        $data = $edition->fresh()->toEditorArray();
        $deleted = $data['paginas'][1]['id'];
        $data['paginas'] = [$data['paginas'][2], $data['paginas'][0]];
        $this->putJson('/admin/periodico/'.$edition->id, $data)->assertOk();
        $saved = $edition->fresh()->toEditorArray();
        $this->assertCount(2, $saved['paginas']);
        $this->assertEquals([1,2], array_column($saved['paginas'],'numero'));
        $this->assertEquals(array_column($data['paginas'],'id'), array_column($saved['paginas'],'id'));
        $this->assertDatabaseMissing('periodico_paginas',['id'=>$deleted]);
        $this->assertDatabaseMissing('periodico_elementos',['pagina_id'=>$deleted]);
        $this->assertEquals('tpl-l18-ref-01', $saved['paginas'][1]['plantilla_id']);
    }

    public function test_cannot_save_an_edition_without_pages(): void
    {
        $edition = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        $this->putJson('/admin/periodico/'.$edition->id, ['paginas'=>[]])->assertUnprocessable();
        $this->assertEquals(1, $edition->paginas()->count());
    }

    public function test_masthead_properties_survive_save_reload_and_render_in_pdf(): void
    {
        $edition = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        $data = $edition->toEditorArray();
        $frame = ['id'=>'cabecera','type'=>'masthead','x'=>31,'y'=>31,'w'=>658,'h'=>160,
            'newspaperName'=>'LATITUD','subBadge'=>'18','motto'=>'Información sin ruido',
            'editionNumber'=>'Edición 6','editionDate'=>'12/10/2026','city'=>'Santa Cruz',
            'price'=>'Bs 5','website'=>'latitud18.ultimahora-tv.com','nameColor'=>'#0B1F3A','accentColor'=>'#D71920',
            'leftEar'=>'','rightEar'=>'','logo_src'=>'/images/periodico/latitud18-placeholder.svg'];
        $data['paginas'][0]['frames'] = [$frame];
        $this->putJson('/admin/periodico/'.$edition->id, $data)->assertOk();
        $saved = $edition->fresh()->toEditorArray();
        foreach (['newspaperName','subBadge','motto','editionNumber','editionDate','city','price','website','logo_src'] as $key) {
            $this->assertEquals($frame[$key], $saved['paginas'][0]['frames'][0][$key]);
        }
        $html = view('periodico.partials.masthead', ['masthead'=>$frame, 'edicion'=>$saved])->render();
        $this->assertStringContainsString('LATITUD', $html);
        $this->assertStringContainsString('12/10/2026', $html);
        $this->assertStringContainsString('Bs 5', $html);
        $GLOBALS['_dompdf_warnings'] = [];
        $this->assertStringStartsWith('%PDF-', app(PeriodicoPdfService::class)->contenido($saved));
        foreach ($GLOBALS['_dompdf_warnings'] ?? [] as $warning) $this->assertStringNotContainsString('Error loading', $warning);
    }

    public function test_page_deletion_is_rolled_back_if_frame_saving_fails(): void
    {
        $edition = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        $page = $edition->paginas()->create(['numero'=>2,'nombre'=>'Segunda página']);
        $data = $edition->fresh()->toEditorArray();
        array_pop($data['paginas']);
        PeriodicoElemento::creating(function () { throw new \RuntimeException('Fallo simulado de guardado'); });
        $this->withoutExceptionHandling();
        try {
            $this->putJson('/admin/periodico/'.$edition->id, $data);
            $this->fail('El guardado debe fallar.');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Fallo simulado de guardado', $e->getMessage());
        }
        $this->assertDatabaseHas('periodico_paginas',['id'=>$page->id]);
        $this->assertEquals(2, $edition->paginas()->count());
    }

    public function test_editor_contains_all_masthead_controls_and_workspace_overlays(): void
    {
        PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        $response = $this->get('/admin/periodico');
        $response->assertOk();
        foreach (['editorWorkspace','inspMastheadBadge','inspMastheadMotto','inspMastheadPrice','inspMastheadLogo','deleteEditorPage'] as $value) {
            $response->assertSee($value, false);
        }
    }
}
