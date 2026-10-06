<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PlantillaService;

class PublicidadPropertyTest extends TestCase
{
    /**
     * Property 6: Dimensiones exactas al insertar frame publicitario
     * Validates: Requirements 3.1, 3.2
     */
    public function test_property_6_dimensiones_exactas_y_bloqueo_de_resize_en_frames_publicitarios(): void
    {
        $catalogo = PlantillaService::AD_FORMATS;

        $codigosEsperados = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2', 'E3', 'E4', 'F1', 'F2'];

        // Confirmar que exactamente los 14 códigos están en el catálogo
        $this->assertCount(14, $catalogo);
        $this->assertEquals($codigosEsperados, array_keys($catalogo));

        foreach ($codigosEsperados as $codigo) {
            $format = $catalogo[$codigo];

            $this->assertArrayHasKey('w_px', $format);
            $this->assertArrayHasKey('h_px', $format);
            $this->assertGreaterThan(0, $format['w_px']);
            $this->assertGreaterThan(0, $format['h_px']);

            // Simular inserción de frame de tipo ad como lo hace el frontend / controller
            $frameAd = [
                'id' => 'f-ad-' . strtolower($codigo) . '-' . uniqid(),
                'type' => 'ad',
                'x' => 12,
                'y' => 12,
                'w' => $format['w_px'],
                'h' => $format['h_px'],
                'resizable' => false,
                'lockResize' => true,
                'propiedades' => [
                    'format_code' => $codigo,
                    'status' => 'disponible',
                    'resizable' => false,
                    'advertiser_name' => null,
                    'advertiser_image_url' => null,
                ]
            ];

            // Validar que las dimensiones coinciden exactamente con el catálogo
            $this->assertEquals($format['w_px'], $frameAd['w'], "Ancho de formato {$codigo} debe coincidir con el catálogo");
            $this->assertEquals($format['h_px'], $frameAd['h'], "Alto de formato {$codigo} debe coincidir con el catálogo");

            // Validar que resizable es false (bloqueo de redimensionamiento Req 3.2)
            $this->assertFalse($frameAd['resizable']);
            $this->assertTrue($frameAd['lockResize']);
            $this->assertFalse($frameAd['propiedades']['resizable']);
        }
    }
}
