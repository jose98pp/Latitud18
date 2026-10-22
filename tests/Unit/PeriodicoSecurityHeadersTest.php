<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class PeriodicoSecurityHeadersTest extends TestCase
{
    public function test_reader_allows_blob_page_textures_and_pdf_fallback(): void
    {
        $request = Request::create('/periodico');
        $request->setRouteResolver(fn () => (new Route('GET', 'periodico', fn () => ''))->name('periodico.public.index'));
        $response = (new SecurityHeadersMiddleware)->handle($request, fn () => response('reader'));
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("img-src 'self' blob:", $policy);
        $this->assertStringContainsString("frame-src 'self' blob:", $policy);
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    public function test_other_pages_do_not_gain_blob_permission(): void
    {
        $request = Request::create('/admin');
        $request->setRouteResolver(fn () => (new Route('GET', 'admin', fn () => ''))->name('admin.dashboard'));
        $response = (new SecurityHeadersMiddleware)->handle($request, fn () => response('admin'));
        $this->assertStringNotContainsString('blob:', $response->headers->get('Content-Security-Policy'));
    }
}
