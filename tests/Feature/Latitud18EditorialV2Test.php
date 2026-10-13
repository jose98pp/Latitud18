<?php

namespace Tests\Feature;

use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use App\Services\EdicionService;
use App\Services\PeriodicoPdfService;
use Database\Seeders\Latitud18EditorialV2Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Latitud18EditorialV2Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\Latitud18ReferenciaSeeder::class);
        $this->seed(Latitud18EditorialV2Seeder::class);
    }

    public function test_all_12_pages_have_different_geometry_and_fit_the_printable_area(): void
    {
        $templates = PeriodicoPlantilla::where('id', 'like', 'tpl-l18-v2-%')->orderBy('id')->get();
        $this->assertCount(12, $templates);
        $signatures = [];
        foreach ($templates as $template) {
            $geometry = [];
            foreach ($template->frames as $f) {
                $geometry[] = [$f['type'], $f['x'], $f['y'], $f['w'], $f['h']];
                $this->assertGreaterThanOrEqual(31, $f['x']);
                $this->assertGreaterThanOrEqual(31, $f['y']);
                $this->assertLessThanOrEqual(689, $f['x'] + $f['w']);
                $this->assertLessThanOrEqual(1075, $f['y'] + $f['h']);
                $this->assertContains($f['type'], ['text','image','ad','divider']);
            }
            $signatures[] = json_encode($geometry);
            $this->assertNotEmpty($template->configuracion['section_label']);
        }
        $this->assertCount(12, array_unique($signatures), 'Cambiar solo textos y secciones no cuenta como una distribución nueva.');
    }

    public function test_editorial_has_portraits_and_last_page_has_three_sections(): void
    {
        $editorial = PeriodicoPlantilla::findOrFail('tpl-l18-v2-02');
        $this->assertCount(4, array_filter($editorial->frames, fn($f) => $f['type'] === 'image'));
        $last = PeriodicoPlantilla::findOrFail('tpl-l18-v2-12');
        foreach (['MUNDO','DEPORTES','CULTURA'] as $section) {
            $this->assertTrue(collect($last->frames)->contains(fn($f) => str_contains($f['content'] ?? '', $section)));
        }
    }

    public function test_local_svg_photos_keep_their_aspect_ratio_in_pdf_markup(): void
    {
        $html = view('periodico.tabloide-frames', [
            'p' => ['ancho' => 720, 'alto' => 1106, 'frames' => [[
                'type' => 'image', 'x' => 31, 'y' => 227, 'w' => 200, 'h' => 100, 'z' => 1,
                'src' => '/images/periodico/latitud18-editorial-photo.svg',
            ]]],
            'npSafe' => fn ($value) => $value,
        ])->render();
        preg_match('/<img[^>]+width:([0-9.]+)mm;height:([0-9.]+)mm/', $html, $dimensions);
        $this->assertCount(3, $dimensions);
        $this->assertEqualsWithDelta(1.5, (float) $dimensions[1] / (float) $dimensions[2], 0.001);
    }

    public function test_seeder_preserves_old_catalog_and_saved_editions(): void
    {
        $template = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01');
        $before = $template->frames;
        $edition = $template->crearEdicion();
        $editionBefore = $edition->toEditorArray();
        $this->seed(Latitud18EditorialV2Seeder::class);
        $this->assertEquals($before, $template->fresh()->frames);
        $this->assertEquals($editionBefore['paginas'], $edition->fresh()->toEditorArray()['paginas']);
        $this->assertEquals(12, PeriodicoPlantilla::where('id', 'like', 'tpl-l18-v2-%')->count());
    }

    public function test_new_mapping_creates_12_correct_pages_and_embeds_brand_fonts(): void
    {
        $mapping = PeriodicoPlantillaEdicion::where('nombre', 'Latitud18 · Editorial v2 · 12 páginas')->firstOrFail()->mapping;
        $edition = app(EdicionService::class)->crearDesdeMapping($mapping, ['numero_edicion'=>'Prueba v2','fecha'=>'12/10/2026']);
        foreach ($edition->paginas as $i => $page) {
            $this->assertEquals('tpl-l18-v2-'.str_pad($i+1,2,'0',STR_PAD_LEFT), $page->plantilla_id);
            $this->assertEquals(1106, $page->alto);
            $this->assertEquals($page->configuracion['section_label'], $page->seccion);
        }
        $pdf = app(PeriodicoPdfService::class)->contenido($edition->toEditorArray());
        preg_match_all('/\/Type\s*\/Page\b/', $pdf, $matches);
        $this->assertCount(12, $matches[0]);
        $this->assertStringContainsString('BebasNeue-Regular', $pdf);
        $this->assertStringContainsString('Montserrat', $pdf);
    }
}
