<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Comentario;

class ComentarioPublicController extends Controller
{
    /**
     * Guarda un comentario enviado por un lector
     */
    public function store(Request $request, $noticiaId)
    {
        $noticia = Noticia::where('publicada', true)->findOrFail($noticiaId);

        // Protección anti-spam con honeypot
        if (!empty($request->input('website_hp'))) {
            return redirect()->back()->with('error', 'Spam detectado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'contenido' => 'required|string|min:3|max:2000',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'contenido.required' => 'El comentario no puede estar vacío.',
            'contenido.min' => 'El comentario debe tener al menos 3 caracteres.',
        ]);

        Comentario::create([
            'noticia_id' => $noticia->id,
            'nombre' => strip_tags(trim($validated['nombre'])),
            'email' => !empty($validated['email']) ? strip_tags(trim($validated['email'])) : null,
            'contenido' => strip_tags(trim($validated['contenido'])),
            'aprobado' => true, // Aprobado por defecto (configurable por moderación)
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('comment_success', '¡Gracias por tu comentario! Ha sido publicado.');
    }
}
