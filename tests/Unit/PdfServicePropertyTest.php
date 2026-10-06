<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PeriodicoPdfService;

class PdfServicePropertyTest extends TestCase
{
    protected PeriodicoPdfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeriodicoPdfService();
    }

    /**
     * Property 10: Patrón de nombre del archivo PDF
     * Validates: Requirements 7.4
     */
    public function test_property_10_patron_de_nombre_del_archivo_pdf(): void
    {
        $testCases = [
            ['id' => '142', 'expected' => 'latitud-18-ed-142.pdf'],
            ['id' => 'ed-2026-oct', 'expected' => 'latitud-18-ed-ed-2026-oct.pdf'],
            ['id' => 'ed_#12$*abc', 'expected' => 'latitud-18-ed-ed_12abc.pdf'],
        ];

        foreach ($testCases as $tc) {
            $filename = $this->service->filename(['id' => $tc['id']]);

            $this->assertStringStartsWith('latitud-18-ed-', $filename);
            $this->assertStringEndsWith('.pdf', $filename);
            $this->assertEquals($tc['expected'], $filename);
        }
    }

    /**
     * Property 11: Headers HTTP siempre presentes en respuesta PDF (renderizado DomPDF)
     * Validates: Requirements 7.6
     */
    public function test_property_11_headers_http_siempre_presentes_en_respuesta_pdf(): void
    {
        $edicionData = [
            'id' => 'ed-test-prop11',
            'titulo' => 'Edición Header Test',
            'numero_edicion' => 'Ed 999',
            'fecha' => '5 de Octubre de 2026',
            'paginas' => [
                [
                    'id' => 'pg-1',
                    'numero' => 1,
                    'seccion' => 'Portada',
                    'nombre' => 'Página 1',
                    'frames' => []
                ]
            ]
        ];

        $response = $this->service->descarga($edicionData);

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=600', $response->headers->get('Cache-Control'));
    }
}
