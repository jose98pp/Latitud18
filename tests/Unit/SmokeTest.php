<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PlantillaService;
use App\Models\PeriodicoPlantilla;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SmokeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test: Factory seeders crean plantillas para las 8 categorías con is_custom = false
     * Validates: Req 1.1
     */
    public function test_factory_seeders_create_plantillas_for_sections_with_is_custom_false(): void
    {
        $factoryPlantillas = PeriodicoPlantilla::where('is_custom', false)->get();
        $this->assertGreaterThanOrEqual(8, $factoryPlantillas->count());

        $expectedSlugs = [
            'portada-default',
            'editorial-default',
            'politica-a',
            'politica-b',
            'santacruz-a',
            'santacruz-b',
            'pais-a',
            'pais-b',
            'economia-a',
            'economia-b',
            'seguridad-default',
            'mundo-default'
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertTrue(
                $factoryPlantillas->contains('slug', $slug),
                "No se encontró la plantilla factory con slug: {$slug}"
            );
        }
    }

    /**
     * Test: Cada plantilla factory tiene configuracion con los valores fijos de retícula
     * Validates: Req 1.3
     */
    public function test_factory_plantillas_have_fixed_grid_configuration(): void
    {
        $factoryPlantillas = PeriodicoPlantilla::where('is_custom', false)->get();

        foreach ($factoryPlantillas as $p) {
            $config = $p->configuracion;
            $this->assertIsArray($config);
            $this->assertEquals(5, $config['columns'] ?? null);
            $this->assertEquals(144, $config['column_width_px'] ?? null);
            $this->assertEquals(4, $config['gutter_px'] ?? null);
            $this->assertEquals(12, $config['margin_px'] ?? null);
            $this->assertEquals(720, $config['page_width_px'] ?? null);
            $this->assertEquals(1040, $config['page_height_px'] ?? null);
        }
    }

    /**
     * Test: Catálogo AD_FORMATS en PlantillaService tiene exactamente 14 entradas con dimensiones correctas
     * Validates: Req 3.1
     */
    public function test_ad_formats_catalog_has_exactly_14_entries_with_valid_dimensions(): void
    {
        $formats = PlantillaService::AD_FORMATS;
        $this->assertCount(14, $formats);

        $expectedKeys = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2', 'E3', 'E4', 'F1', 'F2'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $formats);
            $fmt = $formats[$key];
            $this->assertGreaterThan(0, $fmt['w_px']);
            $this->assertGreaterThan(0, $fmt['h_px']);
            $this->assertGreaterThan(0, $fmt['w_cm']);
            $this->assertGreaterThan(0, $fmt['h_cm']);
            $this->assertNotEmpty($fmt['label']);
        }

        // Exact dimensions check
        $this->assertEquals(932, $formats['A1']['w_px']);
        $this->assertEquals(227, $formats['A1']['h_px']);
        $this->assertEquals(499, $formats['B1']['w_px']);
        $this->assertEquals(983, $formats['B1']['h_px']);
        $this->assertEquals(1006, $formats['C1']['w_px']);
        $this->assertEquals(484, $formats['C1']['h_px']);
        $this->assertEquals(242, $formats['D1']['w_px']);
        $this->assertEquals(492, $formats['D1']['h_px']);
        $this->assertEquals(499, $formats['E1']['w_px']);
        $this->assertEquals(235, $formats['E1']['h_px']);
        $this->assertEquals(1006, $formats['F1']['w_px']);
        $this->assertEquals(182, $formats['F1']['h_px']);
    }

    /**
     * Test: pdf.blade.php incluye declaraciones @font-face para Montserrat y Bebas Neue
     * Validates: Req 9.5
     */
    public function test_pdf_blade_includes_corporate_font_face_declarations(): void
    {
        $pdfBladePath = resource_path('views/periodico/pdf.blade.php');
        $this->assertFileExists($pdfBladePath);

        $content = file_get_contents($pdfBladePath);
        $this->assertStringContainsString("@font-face", $content);
        $this->assertStringContainsString("font-family: 'Montserrat'", $content);
        $this->assertStringContainsString("font-family: 'Bebas Neue'", $content);
        $this->assertStringContainsString("Montserrat-Regular.ttf", $content);
        $this->assertStringContainsString("BebasNeue-Regular.ttf", $content);
    }
}
