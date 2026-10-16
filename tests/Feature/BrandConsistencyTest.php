<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandConsistencyTest extends TestCase
{
    /**
     * Test admin login page has Latitud 18 branding and no UHTV references
     */
    public function test_admin_login_page_branding(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Iniciar Sesión - Latitud 18 Admin');
        $response->assertSee('favicon.svg');
        $response->assertSee('favicon.ico');
        $response->assertSee('LATITUD');
        $response->assertSee('admin@latitud18.com');
        $response->assertDontSee('UHTV');
    }

    /**
     * Test forgot password page branding
     */
    public function test_forgot_password_page_branding(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Recuperar Contraseña - Latitud 18 Admin');
        $response->assertSee('favicon.svg');
        $response->assertSee('LATITUD');
        $response->assertSee('admin@latitud18.com');
        $response->assertDontSee('UHTV');
    }

    /**
     * Test homepage has Latitud 18 branding and proper favicons
     */
    public function test_homepage_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Latitud 18');
        $response->assertSee('favicon.svg');
        $response->assertSee('favicon.ico');
        $response->assertDontSee('UHTV Admin');
        $response->assertDontSee('UHTV Transmisión Digital En Vivo');
        $response->assertDontSee('LATITUD 18 / UHTV');
    }

    /**
     * Test error 404 page has Latitud 18 branding
     */
    public function test_404_page_branding(): void
    {
        $response = $this->get('/non-existent-page-for-testing-404');

        $response->assertStatus(404);
        $response->assertSee('Página No Encontrada - Latitud 18');
        $response->assertSee('Latitud 18');
        $response->assertSee('favicon.svg');
        $response->assertDontSee('UHTV');
    }

    /**
     * Test favicon files exist and are non-empty
     */
    public function test_favicon_assets_exist(): void
    {
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('images/Logo.jpg'));

        $this->assertGreaterThan(50, filesize(public_path('favicon.svg')));
        $this->assertGreaterThan(50, filesize(public_path('favicon.ico')));
        $this->assertGreaterThan(500, filesize(public_path('images/Logo.jpg')));
    }

    /**
     * Test periodico reader page branding and loading screen
     */
    public function test_periodico_reader_branding(): void
    {
        $response = $this->get('/periodico');

        $response->assertStatus(200);
        $response->assertSee('Latitud 18');
        $response->assertDontSee('UHTV');
    }
}
