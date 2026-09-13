<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorageLink
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo ejecutar en producción o cuando sea necesario
        if (app()->environment('production') || !$this->storageLinkExists()) {
            $this->ensureStorageLink();
        }

        return $next($request);
    }

    /**
     * Verificar si el enlace simbólico existe
     */
    private function storageLinkExists(): bool
    {
        $publicStoragePath = public_path('storage');
        return File::exists($publicStoragePath) && is_link($publicStoragePath);
    }

    /**
     * Crear enlace simbólico si no existe
     */
    private function ensureStorageLink(): void
    {
        try {
            $publicStoragePath = public_path('storage');
            $storagePath = storage_path('app/public');

            // Si existe pero no es un enlace simbólico, eliminarlo
            if (File::exists($publicStoragePath) && !is_link($publicStoragePath)) {
                if (File::isDirectory($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                } else {
                    File::delete($publicStoragePath);
                }
            }

            // Crear el enlace simbólico si no existe
            if (!File::exists($publicStoragePath)) {
                if (function_exists('symlink')) {
                    symlink($storagePath, $publicStoragePath);
                    Log::info('Enlace simbólico de storage creado exitosamente');
                } else {
                    Log::warning('La función symlink no está disponible en este servidor');
                }
            }

        } catch (\Exception $e) {
            Log::error('Error al crear enlace simbólico de storage', [
                'error' => $e->getMessage()
            ]);
        }
    }
}