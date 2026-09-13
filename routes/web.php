<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\ProfileController;

// ---------------------------------
// Rutas Públicas (Sin autenticación)
// ---------------------------------

// Ruta principal (Portada)
Route::get('/', [PortadaController::class, 'index'])->name('portada');

// Ruta para mostrar las noticias por categoría
Route::get('/categoria/{id}', [PortadaController::class, 'noticiasPorCategoria'])->name('categoria.noticias');

// Ruta para mostrar el detalle de una noticia
Route::get('/noticia/{id}', [PortadaController::class, 'show'])->name('show');

// Ruta para búsqueda de noticias
Route::get('/buscar', [PortadaController::class, 'search'])->name('search');

// Sitemap XML para Google y motores de búsqueda
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Ruta para comentarios públicos en noticias
Route::post('/noticia/{id}/comentarios', [\App\Http\Controllers\ComentarioPublicController::class, 'store'])->name('noticias.comentarios.store');

// Ruta para registrar reacciones en noticias
Route::post('/noticia/{id}/reaccionar', [\App\Http\Controllers\ReaccionPublicController::class, 'react'])->name('noticias.react');

// Ruta para suscripción al boletín / newsletter
Route::post('/newsletter/suscribir', [\App\Http\Controllers\NewsletterPublicController::class, 'subscribe'])->name('newsletter.subscribe');

// Rutas Públicas del Periódico Digital
Route::get('/periodico', [\App\Http\Controllers\PeriodicoPublicController::class, 'index'])->name('periodico.public.index');
Route::get('/periodico/edicion/{id}', [\App\Http\Controllers\PeriodicoPublicController::class, 'show'])->name('periodico.public.show');
Route::get('/periodico/edicion/{id}/pdf', [\App\Http\Controllers\PeriodicoPublicController::class, 'pdf'])->name('periodico.public.pdf');

// Rutas Públicas de Opinión & Columnistas
Route::get('/opinion', [\App\Http\Controllers\OpinionPublicController::class, 'index'])->name('opinion.index');
Route::get('/opinion/columnista/{id}', [\App\Http\Controllers\OpinionPublicController::class, 'columnista'])->name('opinion.columnista');
Route::get('/opinion/articulo/{id}', [\App\Http\Controllers\OpinionPublicController::class, 'articulo'])->name('opinion.articulo');

// Ruta SEO Amigable para noticias: /{categoria}/{titulo-slug}/{id}
Route::get('/{categoria}/{slug}/{id}', [PortadaController::class, 'showBySlug'])->name('noticias.show.slug')->where('id', '[0-9]+');


// Ruta de prueba para imágenes (solo en desarrollo)
if (app()->environment('local')) {
    Route::get('/test-images', function() {
        $imageService = app(\App\Services\ImageValidationService::class);
        
        return response()->json([
            'default_image_info' => $imageService->getImageInfo(null),
            'storage_link_exists' => is_link(public_path('storage')),
            'images_directory_exists' => is_dir(public_path('images')),
            'default_svg_exists' => file_exists(public_path('images/default-news.svg')),
        ]);
    });
}

// Dashboard para usuarios normales (redirige a portada)
Route::get('/dashboard', function () {
    return redirect()->route('portada');
})->middleware(['auth'])->name('dashboard');

// ---------------------------------
// Rutas de Administrador (Protegidas por autenticación y middleware)
// ---------------------------------

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard del administrador
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rutas del perfil del administrador
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Rutas para el CRUD de noticias
    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    Route::get('/noticias/filter', [NoticiaController::class, 'filter'])->name('noticias.filter');
    Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');
    Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
    Route::get('/noticias/{id}/edit', [NoticiaController::class, 'edit'])->name('noticias.edit');
    Route::put('/noticias/{id}', [NoticiaController::class, 'update'])->name('noticias.update');
    Route::delete('/noticias/{id}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');
    Route::delete('/noticias/foto/{id}', [NoticiaController::class, 'deleteGalleryPhoto'])->name('noticias.foto.destroy');

    // Rutas para el CRUD de categorías
    Route::resource('categorias', CategoryController::class);

    // Rutas para el CRUD de banners
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);

    // Rutas para Suscriptores al Boletín (Newsletter)
    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::get('/newsletter/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'exportCsv'])->name('newsletter.export');
    Route::patch('/newsletter/{id}/toggle', [\App\Http\Controllers\Admin\NewsletterController::class, 'toggleActive'])->name('newsletter.toggle');
    Route::delete('/newsletter/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');

    // Rutas para Opinión y Columnistas
    Route::get('/opinion', [\App\Http\Controllers\Admin\OpinionController::class, 'index'])->name('opinion.index');
    Route::get('/opinion/columnistas/create', [\App\Http\Controllers\Admin\OpinionController::class, 'createColumnista'])->name('opinion.columnistas.create');
    Route::post('/opinion/columnistas', [\App\Http\Controllers\Admin\OpinionController::class, 'storeColumnista'])->name('opinion.columnistas.store');
    Route::get('/opinion/columnistas/{id}/edit', [\App\Http\Controllers\Admin\OpinionController::class, 'editColumnista'])->name('opinion.columnistas.edit');
    Route::put('/opinion/columnistas/{id}', [\App\Http\Controllers\Admin\OpinionController::class, 'updateColumnista'])->name('opinion.columnistas.update');
    Route::delete('/opinion/columnistas/{id}', [\App\Http\Controllers\Admin\OpinionController::class, 'destroyColumnista'])->name('opinion.columnistas.destroy');

    Route::get('/opinion/articulos/create', [\App\Http\Controllers\Admin\OpinionController::class, 'createArticulo'])->name('opinion.articulos.create');
    Route::post('/opinion/articulos', [\App\Http\Controllers\Admin\OpinionController::class, 'storeArticulo'])->name('opinion.articulos.store');
    Route::get('/opinion/articulos/{id}/edit', [\App\Http\Controllers\Admin\OpinionController::class, 'editArticulo'])->name('opinion.articulos.edit');
    Route::put('/opinion/articulos/{id}', [\App\Http\Controllers\Admin\OpinionController::class, 'updateArticulo'])->name('opinion.articulos.update');
    Route::delete('/opinion/articulos/{id}', [\App\Http\Controllers\Admin\OpinionController::class, 'destroyArticulo'])->name('opinion.articulos.destroy');

    // Rutas para el diseñador de periódico digital
    Route::get('/periodico', [\App\Http\Controllers\Admin\PeriodicoController::class, 'index'])->name('periodico.index');
    Route::post('/periodico', [\App\Http\Controllers\Admin\PeriodicoController::class, 'store'])->name('periodico.store');
    Route::put('/periodico/{id}', [\App\Http\Controllers\Admin\PeriodicoController::class, 'update'])->name('periodico.update');
    Route::post('/periodico/{id}/publish', [\App\Http\Controllers\Admin\PeriodicoController::class, 'publish'])->name('periodico.publish');
    Route::post('/periodico/{id}/upload-pdf', [\App\Http\Controllers\Admin\PeriodicoController::class, 'uploadPdf'])->name('periodico.uploadPdf');
    Route::post('/periodico/upload-image', [\App\Http\Controllers\Admin\PeriodicoController::class, 'uploadImage'])->name('periodico.uploadImage');
    Route::delete('/periodico/{id}', [\App\Http\Controllers\Admin\PeriodicoController::class, 'destroy'])->name('periodico.destroy');
    Route::get('/periodico/{id}/pdf', [\App\Http\Controllers\Admin\PeriodicoController::class, 'generatePdf'])->name('periodico.pdf');
    Route::get('/periodico/noticias/{categoryId}', [\App\Http\Controllers\Admin\PeriodicoController::class, 'getNoticiasByCategory'])->name('periodico.noticias');

    // Rutas para Configuración del Medio y Streaming
    Route::get('/configuracion', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Rutas para Moderación de Comentarios
    Route::get('/comentarios', [\App\Http\Controllers\Admin\ComentarioController::class, 'index'])->name('comentarios.index');
    Route::patch('/comentarios/{id}/toggle', [\App\Http\Controllers\Admin\ComentarioController::class, 'toggleAprobado'])->name('comentarios.toggle');
    Route::delete('/comentarios/{id}', [\App\Http\Controllers\Admin\ComentarioController::class, 'destroy'])->name('comentarios.destroy');

    // Ruta para cerrar sesión (logout)
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ---------------------------------
// Ruta de Login exclusivo para administrador
// ---------------------------------

// Rutas de login para admin
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
});
    
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Incluye las rutas de autenticación generadas automáticamente por Laravel
require __DIR__ . '/auth.php';
