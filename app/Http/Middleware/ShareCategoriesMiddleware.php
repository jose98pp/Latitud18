<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class ShareCategoriesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Compartir categorías con todas las vistas usando caché
        $categorias = Cache::remember('navigation_categories', 600, function () {
            return Category::orderBy('name')->get();
        });

        View::share('categorias', $categorias);

        return $next($request);
    }
}
