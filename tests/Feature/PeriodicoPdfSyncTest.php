<?php

namespace Tests\Feature;

use App\Models\PeriodicoEdicion;
use App\Models\User;
use App\Services\PeriodicoPdfService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeriodicoPdfSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function edition(): PeriodicoEdicion
    {
        $edition = PeriodicoEdicion::create([
            'id' => 'pdf-sync-regression', 'numero_edicion' => 'Prueba', 'fecha' => '20/10/2026',
            'publicada' => true, 'estado' => 'publicado', 'activa' => true,
            'pdf_url' => '/ediciones_pdf/demo_edicion.pdf',
        ]);
        $page = $edition->paginas()->create([
            'numero' => 1, 'nombre' => 'Portada', 'ancho' => 720, 'alto' => 1106,
            'configuracion' => ['page_width_mm' => 280, 'page_height_mm' => 430],
        ]);
        $page->crearElementoDesdeFrame(['id'=>'article', 'type'=>'article', 'x'=>31, 'y'=>40, 'w'=>658, 'h'=>220,
            'titular'=>'Titular anterior', 'cuerpo'=>'Cuerpo anterior', 'columns'=>2]);
        return $edition->fresh();
    }

    public function test_saved_news_images_and_geometry_replace_uploaded_pdf_in_admin_and_reader(): void
    {
        Storage::fake('public');
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        $edition = $this->edition();
        $this->get('/periodico/edicion/'.$edition->id.'/documento')->assertOk();
        $upload = $this->post('/admin/periodico/upload-image', ['image'=>UploadedFile::fake()->image('news.png', 80, 40)])->assertOk();
        $imageFile = Storage::disk('public')->path('periodico/'.$upload->json('filename'));
        try {
            $data = $edition->toEditorArray();
            $data['paginas'][0]['frames'][0]['titular'] = '<b>Titular corregido</b>';
            $data['paginas'][0]['frames'][0]['cuerpo'] = '<p>Noticia creada y editada con su contenido completo.</p>';
            $data['paginas'][0]['frames'][0]['x'] = 62;
            $data['paginas'][0]['frames'][0]['article_title_style'] = ['fontSize'=>'24px', 'color'=>'#D71920'];
            $data['paginas'][0]['frames'][] = ['id'=>'photo', 'type'=>'image', 'x'=>130, 'y'=>310, 'w'=>300, 'h'=>250,
                'src'=>$upload->json('url'), 'image_fit'=>'contain', 'caption'=>'Fotografía guardada'];
            $save = $this->putJson('/admin/periodico/'.$edition->id, $data)->assertOk();
            $save->assertJsonPath('edicion.pdf_url', null)->assertJsonPath('edicion.paginas.0.frames.0.x', 62);
            $saved = $edition->fresh()->toEditorArray();
            $this->assertSame($data['paginas'][0]['frames'][0]['cuerpo'], $saved['paginas'][0]['frames'][0]['cuerpo']);
            $html = view('periodico.pdf', ['edicion'=>$saved])->render();
            foreach (['<b>Titular corregido</b>', 'Noticia creada y editada', 'Fotografía guardada', $imageFile] as $value) $this->assertStringContainsString($value, $html);
            $service = app(PeriodicoPdfService::class);
            $this->assertEquals(['fontSize'=>'24px', 'color'=>'#D71920'], $saved['paginas'][0]['frames'][0]['article_title_style']);
            $this->assertStringContainsString($service->estiloTextoParaPdf($saved['paginas'][0]['frames'][0]['article_title_style'], min(280/720,430/1106)), $html);
            $this->assertSame($imageFile, $service->imagenParaPdf($upload->json('url')));
            $reader = $this->get('/periodico/edicion/'.$edition->id.'/documento')->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $reader->getContent());
            $this->assertNotEquals(file_get_contents(public_path('ediciones_pdf/demo_edicion.pdf')), $reader->getContent());
            $this->assertMatchesRegularExpression('#/Subtype\s*/Image#', $reader->getContent());
            $this->get('/admin/periodico/'.$edition->id.'/pdf')->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Cache-Control', 'no-store, private');
            // El payload antiguo no puede volver a imponer el PDF subido; el lector cambia de revisión.
            $data['paginas'][0]['frames'][0]['cuerpo'] = '<p>Segunda corrección de la noticia.</p>';
            $this->putJson('/admin/periodico/'.$edition->id, $data)->assertOk()->assertJsonPath('edicion.pdf_url', null);
            $new = $this->get('/periodico/edicion/'.$edition->id.'/documento')->assertOk();
            $this->assertNotEquals($reader->headers->get('ETag'), $new->headers->get('ETag'));
        } finally {
            @unlink($imageFile);
        }
    }

    public function test_unchanged_editor_does_not_discard_uploaded_pdf(): void
    {
        $edition = $this->edition();
        $edition->syncFromEditorData($edition->toEditorArray());
        $this->assertSame('/ediciones_pdf/demo_edicion.pdf', $edition->fresh()->pdf_url);
    }

    public function test_storage_photos_and_embedded_news_images_are_resolved_without_http(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('photo.png', 160, 80);
        Storage::disk('public')->put('news/photo.png', file_get_contents($file->getRealPath()));
        $service = app(PeriodicoPdfService::class);
        $local = Storage::disk('public')->path('news/photo.png');
        $this->assertSame($local, $service->imagenParaPdf('https://previous-domain.test/storage/news/photo.png'));
        $this->assertSame('', $service->imagenParaPdf('/storage/../../.env'));
        $edition = ['paginas'=>[['ancho'=>720,'alto'=>1040,'frames'=>[
            ['id'=>'legacy-article','type'=>'article','w'=>600,'h'=>350,'content'=>'<p>Cuerpo legado con foto <img src="/storage/news/photo.png"></p>'],
        ]]]];
        $html = view('periodico.pdf', ['edicion'=>$edition])->render();
        $this->assertStringContainsString($local, $html);
        $this->assertStringContainsString('Cuerpo legado con foto', strip_tags($html));
        $GLOBALS['_dompdf_warnings'] = [];
        $bytes = $service->contenido($edition);
        $this->assertMatchesRegularExpression('#/Subtype\s*/Image#', $bytes);
        foreach ($GLOBALS['_dompdf_warnings'] ?? [] as $warning) $this->assertStringNotContainsString('Error loading', $warning);
    }

    public function test_pdf_columns_preserve_all_text_and_rich_formatting(): void
    {
        $html = '<p style="color:red"><b>Uno dos tres cuatro</b> cinco seis</p><p>Siete ocho nueve diez once doce</p>';
        $columns = app(PeriodicoPdfService::class)->columnasParaPdf($html, 3);
        $this->assertCount(3, $columns);
        $this->assertSame('Uno dos tres cuatro cinco seisSiete ocho nueve diez once doce', trim(strip_tags(implode('', $columns))));
        $this->assertStringContainsString('style="color:red"', $columns[0]);
        $this->assertStringContainsString('<b>', $columns[0]);
    }
}
