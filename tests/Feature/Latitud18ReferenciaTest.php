<?php

namespace Tests\Feature;

use App\Models\PeriodicoPlantilla;
use App\Models\PeriodicoPlantillaEdicion;
use App\Models\User;
use App\Services\EdicionService;
use App\Services\PeriodicoPdfService;
use App\Services\PlantillaService;
use Database\Seeders\Latitud18ReferenciaSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Latitud18ReferenciaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        $this->seed(Latitud18ReferenciaSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_catalog_has_18_templates_and_all_frames_fit_the_printable_area(): void
    {
        $templates = PeriodicoPlantilla::where('id', 'like', 'tpl-l18-%')->get();
        $this->assertCount(18, $templates);
        foreach ($templates as $template) {
            $config = $template->configuracion;
            $this->assertEquals(280, $config['page_width_mm']);
            $this->assertEquals(430, $config['page_height_mm']);
            foreach ($template->frames as $frame) {
                $this->assertGreaterThan(0, $frame['w']);
                $this->assertGreaterThan(0, $frame['h']);
                $this->assertGreaterThanOrEqual(31, $frame['x']);
                $this->assertGreaterThanOrEqual(31, $frame['y']);
                $this->assertLessThanOrEqual(689, $frame['x'] + $frame['w']);
                $this->assertLessThanOrEqual(1075, $frame['y'] + $frame['h']);
            }
        }
    }

    public function test_seeder_can_run_twice_without_overwriting_user_edits(): void
    {
        $template = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01');
        $template->update(['nombre' => 'Portada personalizada']);
        $this->seed(Latitud18ReferenciaSeeder::class);
        $this->assertEquals('Portada personalizada', $template->fresh()->nombre);
        $this->assertEquals(18, PeriodicoPlantilla::where('id', 'like', 'tpl-l18-%')->count());
        $this->assertEquals(1, PeriodicoPlantillaEdicion::where('nombre', 'Latitud18 · Edición semanal de 12 páginas')->count());
    }

    public function test_full_edition_has_12_independent_pages_with_the_correct_dimensions(): void
    {
        $mapping = PeriodicoPlantillaEdicion::where('nombre', 'Latitud18 · Edición semanal de 12 páginas')->firstOrFail()->mapping;
        $edition = app(EdicionService::class)->crearDesdeMapping($mapping, ['numero_edicion' => 'Prueba', 'fecha' => '08/10/2026']);
        $this->assertCount(12, $edition->paginas);
        foreach ($edition->paginas as $page) {
            $this->assertEquals(720, $page->ancho);
            $this->assertEquals(1106, $page->alto);
            $this->assertEquals(280, $page->configuracion['page_width_mm']);
        }
        $frame = $edition->paginas->first()->elementos->first();
        $frame->update(['contenido' => 'Contenido editado']);
        $this->assertNotEquals('Contenido editado', PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->frames[0]['content']);
    }

    public function test_page_template_survives_save_and_reload_with_image_fit(): void
    {
        $edition = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01')->crearEdicion();
        $data = $edition->toEditorArray();
        foreach ($data['paginas'][0]['frames'] as &$frame) {
            if ($frame['type'] === 'image') $frame['image_fit'] = 'contain';
        }
        unset($frame);
        $this->actingAs($this->admin())->putJson('/admin/periodico/'.$edition->id, $data)
            ->assertOk()->assertJsonPath('edicion.paginas.0.alto', 1106);
        $saved = $edition->fresh()->toEditorArray()['paginas'][0];
        $this->assertEquals(280, $saved['configuracion']['page_width_mm']);
        $image = collect($saved['frames'])->firstWhere('type', 'image');
        $this->assertEquals('contain', $image['image_fit']);
    }

    public function test_import_store_export_duplicate_and_apply_preserve_configuration(): void
    {
        $this->actingAs($this->admin());
        $template = PeriodicoPlantilla::findOrFail('tpl-l18-ref-01');
        $data = app(PlantillaService::class)->exportar($template);
        $file = UploadedFile::fake()->createWithContent('portada.latitud-template', json_encode($data));
        $import = $this->post('/admin/periodico/templates/import', ['template_file' => $file], ['Accept' => 'application/json']);
        $import->assertOk()->assertJsonPath('template.configuracion.page_height_px', 1106);
        $id = $import->json('template.id');
        $this->assertEquals(280, PeriodicoPlantilla::findOrFail($id)->configuracion['page_width_mm']);
        $store = $this->postJson('/admin/periodico/templates', $data);
        $store->assertOk()->assertJsonPath('template.configuracion.page_width_mm', 280);
        $this->postJson('/admin/periodico/templates/'.$id.'/duplicate')->assertOk();
        $edition = $template->crearEdicion();
        $page = $edition->paginas->first();
        $page->update(['alto' => 1040, 'configuracion' => null]);
        $this->postJson('/admin/periodico/templates/'.$id.'/apply/'.$page->id)
            ->assertOk()->assertJsonPath('pagina.alto', 1106);
        $export = $this->get('/admin/periodico/templates/'.$id.'/export');
        $export->assertOk();
        $export->assertJsonPath('configuracion.page_width_mm', 280);
    }

    public function test_ad_catalog_uses_the_canvas_scale_and_fits_256_mm(): void
    {
        foreach (PlantillaService::AD_FORMATS as $format) {
            $this->assertLessThanOrEqual(25.6, $format['w_cm']);
            $this->assertEqualsWithDelta($format['w_cm']*720/28, $format['w_px'], 0.5);
            $this->assertLessThanOrEqual(658, $format['w_px']);
        }
    }

    public function test_pdf_has_exactly_12_pages_of_280_by_430_mm(): void
    {
        $mapping = PeriodicoPlantillaEdicion::where('nombre', 'Latitud18 · Edición semanal de 12 páginas')->firstOrFail()->mapping;
        $edition = app(EdicionService::class)->crearDesdeMapping($mapping, ['numero_edicion' => 'PDF', 'fecha' => '08/10/2026']);
        $GLOBALS['_dompdf_warnings'] = [];
        $pdf = app(PeriodicoPdfService::class)->contenido($edition->toEditorArray());
        foreach ($GLOBALS['_dompdf_warnings'] ?? [] as $warning) {
            $this->assertStringNotContainsString('Error loading', $warning, 'El PDF debe cargar las imágenes y fuentes locales.');
        }
        $this->assertStringStartsWith('%PDF-', $pdf);
        preg_match_all('/\/Type\s*\/Page\b/', $pdf, $pages);
        $this->assertCount(12, $pages[0]);
        preg_match_all('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $boxes, PREG_SET_ORDER);
        $this->assertNotEmpty($boxes);
        foreach ($boxes as $box) {
            $this->assertEqualsWithDelta(280*72/25.4, (float)$box[1], 0.1);
            $this->assertEqualsWithDelta(430*72/25.4, (float)$box[2], 0.1);
        }
    }
}
