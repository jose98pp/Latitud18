<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ComentarioPublicController;
use App\Http\Controllers\ReaccionPublicController;
use App\Http\Controllers\NewsletterPublicController;
use App\Http\Controllers\PeriodicoPublicController;
use App\Http\Controllers\OpinionPublicController;
use App\Http\Controllers\ContraAtaqueController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OpinionController;
use App\Http\Controllers\Admin\PeriodicoController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\ComentarioController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

/*
|--------------------------------------------------------------------------
| 1. PORTAL PRINCIPAL & NOTICIAS (SEO AMIGABLE)
|--------------------------------------------------------------------------
*/

// Portada
Route::get('/', [PortadaController::class, 'index'])->name('portada');

// Búsqueda en tiempo real
Route::get('/buscar', [PortadaController::class, 'search'])->name('search');

// Sitemap XML para buscadores (Google, Bing)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Categorías por Slug (e.g. /categoria/politica, /categoria/santa-cruz)
Route::get('/categoria/{slug}', [PortadaController::class, 'noticiasPorCategoria'])->name('categoria.noticias');

// Redirección canónica de noticias por ID -> /{categoria}/{slug}/{id}
Route::get('/noticia/{id}', [PortadaController::class, 'show'])->name('show');

// Detalle de Noticia SEO Canónico: /{categoria}/{titulo-slug}/{id}
Route::get('/{categoria}/{slug}/{id}', [PortadaController::class, 'showBySlug'])
    ->name('noticias.show.slug')
    ->where('id', '[0-9]+');

/*
|--------------------------------------------------------------------------
| 2. INTERACCIONES PÚBLICAS (COMENTARIOS, REACCIONES, NEWSLETTER)
|--------------------------------------------------------------------------
*/
Route::post('/noticia/{id}/comentarios', [ComentarioPublicController::class, 'store'])->name('noticias.comentarios.store');
Route::post('/noticia/{id}/reaccionar', [ReaccionPublicController::class, 'react'])->name('noticias.react');
Route::post('/newsletter/suscribir', [NewsletterPublicController::class, 'subscribe'])->name('newsletter.subscribe');

/*
|--------------------------------------------------------------------------
| 3. PORTALES ESPECIALIZADOS
|--------------------------------------------------------------------------
*/

// Periódico Digital Tabloide
Route::prefix('periodico')->name('periodico.public.')->group(function () {
    Route::get('/', [PeriodicoPublicController::class, 'index'])->name('index');
    Route::get('/edicion/{id}', [PeriodicoPublicController::class, 'show'])->name('show');
    Route::get('/edicion/{id}/pdf', [PeriodicoPublicController::class, 'pdf'])->name('pdf');
});

// Opinión, Columnistas & Editoriales
Route::prefix('opinion')->name('opinion.')->group(function () {
    Route::get('/', [OpinionPublicController::class, 'index'])->name('index');
    Route::get('/columnista/{id}', [OpinionPublicController::class, 'columnista'])->name('columnista');
    Route::get('/articulo/{id}', [OpinionPublicController::class, 'articulo'])->name('articulo');
});

// Contra Ataque (Portal Deportivo Multi-deporte)
Route::prefix('contraataque')->name('contraataque.')->group(function () {
    Route::get('/', [ContraAtaqueController::class, 'index'])->name('index');
    Route::get('/noticia/{id}', [ContraAtaqueController::class, 'show'])->name('show');
    Route::get('/seccion/{seccion}', [ContraAtaqueController::class, 'seccion'])->name('seccion');
});
Route::get('/deportes', function () {
    return redirect()->route('contraataque.index');
})->name('deportes.redirect');

/*
|--------------------------------------------------------------------------
| 4. PANEL DE ADMINISTRACIÓN (BACKOFFICE)
|--------------------------------------------------------------------------
*/

// Login Administrativo
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil administrativo
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Gestión de Noticias (CRUD)
    Route::get('/noticias/filter', [NoticiaController::class, 'filter'])->name('noticias.filter');
    Route::delete('/noticias/foto/{id}', [NoticiaController::class, 'deleteGalleryPhoto'])->name('noticias.foto.destroy');
    Route::resource('noticias', NoticiaController::class);

    // Categorías & Banners
    Route::resource('categorias', CategoryController::class);
    Route::resource('banners', BannerController::class);

    // Boletín Informativo (Newsletter)
    Route::prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('/', [NewsletterController::class, 'index'])->name('index');
        Route::get('/export', [NewsletterController::class, 'exportCsv'])->name('export');
        Route::patch('/{id}/toggle', [NewsletterController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{id}', [NewsletterController::class, 'destroy'])->name('destroy');
    });

    // Opinión & Columnistas (CRUD)
    Route::prefix('opinion')->name('opinion.')->group(function () {
        Route::get('/', [OpinionController::class, 'index'])->name('index');
        Route::get('/columnistas/create', [OpinionController::class, 'createColumnista'])->name('columnistas.create');
        Route::post('/columnistas', [OpinionController::class, 'storeColumnista'])->name('columnistas.store');
        Route::get('/columnistas/{id}/edit', [OpinionController::class, 'editColumnista'])->name('columnistas.edit');
        Route::put('/columnistas/{id}', [OpinionController::class, 'updateColumnista'])->name('columnistas.update');
        Route::delete('/columnistas/{id}', [OpinionController::class, 'destroyColumnista'])->name('columnistas.destroy');

        Route::get('/articulos/create', [OpinionController::class, 'createArticulo'])->name('articulos.create');
        Route::post('/articulos', [OpinionController::class, 'storeArticulo'])->name('articulos.store');
        Route::get('/articulos/{id}/edit', [OpinionController::class, 'editArticulo'])->name('articulos.edit');
        Route::put('/articulos/{id}', [OpinionController::class, 'updateArticulo'])->name('articulos.update');
        Route::delete('/articulos/{id}', [OpinionController::class, 'destroyArticulo'])->name('articulos.destroy');
    });

    // Diseñador Editorial de Periódico Digital
    Route::prefix('periodico')->name('periodico.')->group(function () {
        Route::get('/', [PeriodicoController::class, 'index'])->name('index');
        Route::post('/', [PeriodicoController::class, 'store'])->name('store');
        Route::put('/{id}', [PeriodicoController::class, 'update'])->name('update');
        Route::post('/{id}/publish', [PeriodicoController::class, 'publish'])->name('publish');
        Route::post('/{id}/upload-pdf', [PeriodicoController::class, 'uploadPdf'])->name('uploadPdf');
        Route::post('/upload-image', [PeriodicoController::class, 'uploadImage'])->name('uploadImage');
        Route::delete('/{id}', [PeriodicoController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/pdf', [PeriodicoController::class, 'generatePdf'])->name('pdf');
        Route::get('/noticias/{categoryId}', [PeriodicoController::class, 'getNoticiasByCategory'])->name('noticias');
    });

    // Configuración del Portal & Streaming
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Moderación de Comentarios
    Route::get('/comentarios', [ComentarioController::class, 'index'])->name('comentarios.index');
    Route::patch('/comentarios/{id}/toggle', [ComentarioController::class, 'toggleAprobado'])->name('comentarios.toggle');
    Route::delete('/comentarios/{id}', [ComentarioController::class, 'destroy'])->name('comentarios.destroy');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__ . '/auth.php';
