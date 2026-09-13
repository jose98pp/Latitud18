<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Si la ruta contiene 'admin', redirigir al login de admin
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        
        // Para otras rutas, usar el login normal (si existe)
        return route('login');
    }
}
