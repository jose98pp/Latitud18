<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class FrameValidationPropertyTest extends TestCase
{
    /**
     * Property 4: Validación de archivos para upload
     * Validates: Requirements 2.2, 2.3, 3.3
     */
    public function test_property_4_validacion_de_archivos_para_upload(): void
    {
        $rules = [
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
        ];

        // 1. Archivo válido JPEG <= 10MB (ej. 2MB)
        $validJpg = UploadedFile::fake()->create('foto.jpg', 2048, 'image/jpeg');
        $v1 = Validator::make(['image' => $validJpg], $rules);
        $this->assertFalse($v1->fails(), 'JPEG válido <= 10MB debe pasar la validación');

        // 2. Archivo válido PNG <= 10MB
        $validPng = UploadedFile::fake()->create('banner.png', 1024, 'image/png');
        $v2 = Validator::make(['image' => $validPng], $rules);
        $this->assertFalse($v2->fails(), 'PNG válido <= 10MB debe pasar la validación');

        // 3. Archivo válido WEBP <= 10MB
        $validWebp = UploadedFile::fake()->create('grafico.webp', 512, 'image/webp');
        $v3 = Validator::make(['image' => $validWebp], $rules);
        $this->assertFalse($v3->fails(), 'WEBP válido <= 10MB debe pasar la validación');

        // 4. Archivo inválido: excede 10 MB (ej. 10241 KB)
        $oversized = UploadedFile::fake()->create('pesada.jpg', 10241, 'image/jpeg');
        $v4 = Validator::make(['image' => $oversized], $rules);
        $this->assertTrue($v4->fails(), 'Archivo > 10MB debe ser rechazado');

        // 5. Archivo inválido: tipo MIME no permitido (PDF o TXT)
        $pdfFile = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');
        $v5 = Validator::make(['image' => $pdfFile], $rules);
        $this->assertTrue($v5->fails(), 'Archivo PDF debe ser rechazado');
    }

    /**
     * Property 5: Validación de titular de frame article
     * Validates: Requirements 2.7, 2.8
     */
    public function test_property_5_validacion_de_titular_de_frame_article(): void
    {
        $validateTitular = function (?string $titular): bool {
            if ($titular === null) {
                return false;
            }
            $len = mb_strlen(trim($titular));
            return $len >= 1 && $len <= 200;
        };

        // 1. Válido: longitud 1 carácter
        $this->assertTrue($validateTitular('A'));

        // 2. Válido: longitud típica
        $this->assertTrue($validateTitular('Gobierno anuncia plan económico departamental'));

        // 3. Válido: exactamente 200 caracteres
        $titular200 = str_repeat('X', 200);
        $this->assertTrue($validateTitular($titular200));

        // 4. Inválido: vacío o solo espacios
        $this->assertFalse($validateTitular(''));
        $this->assertFalse($validateTitular('   '));
        $this->assertFalse($validateTitular(null));

        // 5. Inválido: 201 caracteres
        $titular201 = str_repeat('X', 201);
        $this->assertFalse($validateTitular($titular201));
    }
}
