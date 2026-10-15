<?php

namespace Tests\Feature;

use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use App\Models\User;
use App\Services\EdicionService;
use App\Services\PeriodicoPdfService;
use Database\Seeders\Latitud18OctubreSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Latitud18OctubreTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\Latitud18ReferenciaSeeder::class);
        $this->seed(Latitud18OctubreSeeder::class);
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    }

    private function mapping(int $count): array
    {
        return PeriodicoPlantillaEdicion::where('nombre', "Latitud18 · Clásica Octubre · {$count} páginas")->firstOrFail()->mapping;
    }

    private function admin(): User
    {
        return User::firstOrCreate(['email' => 'octubre-test@latitud18.com'], [
            'name' => 'Editor de prueba', 'password' => bcrypt('testing'), 'role' => 'admin',
        ]);
    }

    public function test_both_editions_have_unique_layouts_and_every_frame_fits_the_printable_area(): void
    {
        foreach ([12, 28] as $count) {
            $templates = PeriodicoPlantilla::where('id', 'like', "tpl-l18-oct-{$count}-%")->orderBy('id')->get();
            $this->assertCount($count, $templates);
            $signatures = [];
            foreach ($templates as $template) {
                $geometry = [];
                foreach ($template->frames as $frame) {
                    $geometry[] = [$frame['type'], $frame['x'], $frame['y'], $frame['w'], $frame['h']];
                    $this->assertGreaterThanOrEqual(31, $frame['x']);
                    $this->assertGreaterThanOrEqual(31, $frame['y']);
                    $this->assertLessThanOrEqual(689, $frame['x'] + $frame['w']);
                    $this->assertLessThanOrEqual(1075, $frame['y'] + $frame['h']);
                    $this->assertContains($frame['type'], ['text', 'image', 'divider', 'ad']);
                }
                $signatures[] = json_encode($geometry);
                $this->assertEquals(280, $template->configuracion['page_width_mm']);
                $this->assertEquals(430, $template->configuracion['page_height_mm']);
            }
            $this->assertCount($count, array_unique($signatures));
        }
        $legal = PeriodicoPlantilla::where('id', 'like', 'tpl-l18-oct-28-%')->where('categoria', 'avisos')->get();
        $this->assertCount(12, $legal);
        foreach ($legal as $template) {
            $this->assertTrue(collect($template->frames)->contains(fn ($f) => ($f['editorial_role'] ?? '') === 'body'));
            $this->assertFalse(collect($template->frames)->contains(fn ($f) => $f['type'] === 'image'));
        }
    }

    public function test_creating_both_editions_keeps_number_order_sections_and_pdf_page_count(): void
    {
        foreach ([12, 28] as $count) {
            $edition = app(EdicionService::class)->crearDesdeMapping($this->mapping($count), ['numero_edicion' => "Prueba {$count}"]);
            $this->assertEquals($count, $edition->num_paginas);
            $this->assertCount($count, $edition->paginas);
            foreach ($edition->paginas as $i => $page) {
                $this->assertEquals($i + 1, $page->numero);
                $this->assertEquals("tpl-l18-oct-{$count}-".str_pad($i + 1, 2, '0', STR_PAD_LEFT), $page->plantilla_id);
                $this->assertEquals($page->configuracion['section_label'], $page->seccion);
                $this->assertEquals(1106, $page->alto);
            }
            $pdf = app(PeriodicoPdfService::class)->contenido($edition->toEditorArray());
            preg_match_all('/\/Type\s*\/Page\b/', $pdf, $pages);
            $this->assertCount($count, $pages[0]);
            $this->assertStringContainsString('BebasNeue-Regular', $pdf);
            $this->assertStringContainsString('Montserrat', $pdf);
        }
    }

    public function test_extended_model_is_available_and_creates_all_28_pages_over_http(): void
    {
        $this->actingAs($this->admin());
        $mapping = $this->mapping(28);
        $saved = $this->postJson('/admin/periodico/plantilla-edicion', [
            'nombre' => 'Mi edición extendida', 'mapping' => array_reverse($mapping),
        ])->assertCreated()->json('plantilla_edicion');
        $response = $this->postJson('/admin/periodico/plantilla-edicion/'.$saved['id'].'/crear-edicion', [
            'numero_edicion' => 'Prueba 28', 'fecha' => '14/10/2026',
        ])->assertOk()->assertJsonPath('success', true);
        $edition = $response->json('edicion');
        $this->assertCount(28, $edition['paginas']);
        $this->assertEquals('Avisos / Edictos', $edition['paginas'][27]['seccion']);
        $this->assertStringContainsString('28 páginas', $response->json('message'));
        $this->putJson('/admin/periodico/'.$edition['id'], $edition)->assertOk();
        $this->assertEquals(28, PeriodicoEdicion::findOrFail($edition['id'])->paginas()->count());
    }

    public function test_incomplete_or_duplicate_mappings_return_422_without_writing(): void
    {
        $this->actingAs($this->admin());
        $mapping = $this->mapping(28);
        $before = PeriodicoPlantillaEdicion::count();
        $bad = $mapping; $bad[27]['slot'] = 27;
        foreach ([array_slice($mapping, 0, 13), $bad] as $invalid) {
            $this->postJson('/admin/periodico/plantilla-edicion', ['nombre' => 'Inválida', 'mapping' => $invalid])->assertStatus(422);
        }
        $this->assertEquals($before, PeriodicoPlantillaEdicion::count());
        $this->expectException(\InvalidArgumentException::class);
        app(EdicionService::class)->crearDesdeMapping($bad, []);
    }

    public function test_reinstall_preserves_customized_templates_and_saved_editions(): void
    {
        $edition = app(EdicionService::class)->crearDesdeMapping($this->mapping(12), []);
        $before = $edition->toEditorArray();
        $template = PeriodicoPlantilla::findOrFail('tpl-l18-oct-12-01');
        $frames = $template->frames; $frames[0]['content'] = '<p>Mi edición guardada</p>';
        $template->update(['frames' => $frames]);
        $old = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->frames;
        $this->seed(Latitud18OctubreSeeder::class);
        $this->assertEquals($frames, $template->fresh()->frames);
        $this->assertEquals($before['paginas'], $edition->fresh()->toEditorArray()['paginas']);
        $this->assertEquals($old, PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->frames);
        $this->assertEquals(40, PeriodicoPlantilla::where('id', 'like', 'tpl-l18-oct-%')->count());
    }
}
