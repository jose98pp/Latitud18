<?php
namespace Tests\Feature;

use App\Models\PeriodicoEdicion;
use App\Services\PeriodicoPdfService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PeriodicoReaderTest extends TestCase
{
    use DatabaseTransactions;

    private function edition(array $attributes = []): PeriodicoEdicion
    {
        return PeriodicoEdicion::create(array_merge([
            'id' => 'reader-test', 'titulo' => 'Edición de prueba',
            'numero_edicion' => '1', 'fecha' => '16/10/2026',
            'publicada' => true, 'estado' => 'publicado', 'activa' => true,
        ], $attributes));
    }

    public function test_inline_pdf_is_cached_and_supports_conditional_requests(): void
    {
        Cache::flush();
        $this->edition();
        $service = $this->mock(PeriodicoPdfService::class);
        $service->shouldReceive('resolveUploadedPdf')->andReturn(null);
        $service->shouldReceive('contenido')->once()->andReturn('%PDF-1.4 test');
        $response = $this->get('/periodico/edicion/reader-test/documento');
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="latitud18.pdf"');
        $this->get('/periodico/edicion/reader-test/documento', ['If-None-Match' => $response->headers->get('ETag')])->assertStatus(304);
    }

    public function test_unpublished_editions_never_expose_a_pdf(): void
    {
        $this->edition(['publicada' => false]);
        $this->get('/periodico/edicion/reader-test/documento')->assertNotFound();
        PeriodicoEdicion::find('reader-test')->update(['publicada' => true, 'estado' => 'borrador']);
        $this->get('/periodico/edicion/reader-test/documento')->assertNotFound();
    }

    public function test_pdf_failure_returns_a_retryable_error_instead_of_html(): void
    {
        Cache::flush();
        $this->edition();
        $service = $this->mock(PeriodicoPdfService::class);
        $service->shouldReceive('resolveUploadedPdf')->andReturn(null);
        $service->shouldReceive('contenido')->andThrow(new \RuntimeException('Unavailable'));
        $this->get('/periodico/edicion/reader-test/documento')->assertStatus(503)->assertJsonStructure(['message']);
    }

    public function test_reader_uses_pdf_even_when_edition_has_no_editor_pages(): void
    {
        $this->edition(['pdf_url' => '/ediciones_pdf/demo_edicion.pdf']);
        $view = app(\App\Http\Controllers\PeriodicoPublicController::class)->show('reader-test');
        $this->assertSame('periodico.reader', $view->name());
        $this->assertEmpty($view->getData()['edicionActiva']['paginas']);
        $this->assertSame('/ediciones_pdf/demo_edicion.pdf', $view->getData()['edicionActiva']['pdf_url']);
    }

    public function test_changed_content_invalidates_cached_pdf(): void
    {
        Cache::flush();
        $edition = $this->edition();
        $service = $this->mock(PeriodicoPdfService::class);
        $service->shouldReceive('resolveUploadedPdf')->andReturn(null);
        $service->shouldReceive('contenido')->twice()->andReturn('%PDF-first', '%PDF-second');
        $this->assertSame('%PDF-first', $this->get('/periodico/edicion/reader-test/documento')->getContent());
        $edition->update(['titulo' => 'Edición corregida']);
        $this->assertSame('%PDF-second', $this->get('/periodico/edicion/reader-test/documento')->getContent());
    }

    public function test_uploaded_pdf_is_served_without_regenerating_the_layout(): void
    {
        Cache::flush();
        $this->edition(['pdf_url' => '/ediciones_pdf/demo_edicion.pdf']);
        $response = $this->get('/periodico/edicion/reader-test/documento');
        $response->assertOk();
        $this->assertEquals(file_get_contents(public_path('ediciones_pdf/demo_edicion.pdf')), $response->getContent());
    }
}
