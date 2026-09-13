<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UpdateLastLoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Actualizar último login solo si han pasado más de 5 minutos
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) > 5) {
                $user->updateLastLogin();
            }
        }

        return $next($request);
    }
}
