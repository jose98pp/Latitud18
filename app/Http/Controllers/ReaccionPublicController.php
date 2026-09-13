<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\ReaccionNoticia;

class ReaccionPublicController extends Controller
{
    /**
     * Registra o cambia la reacción de un usuario en una noticia
     */
    public function react(Request $request, $id)
    {
        $noticia = Noticia::where('publicada', true)->findOrFail($id);

        $tipo = $request->input('tipo') ?: $request->json('tipo');
        if (!in_array($tipo, ['me_informa', 'interesante', 'me_indigna', 'recomiendo'])) {
            return response()->json(['success' => false, 'message' => 'Tipo de reacción no válido'], 422);
        }

        $ip = $request->ip();

        // Si ya tenía esta misma reacción, la quitamos (toggle)
        $existing = ReaccionNoticia::where('noticia_id', $noticia->id)
            ->where('ip_address', $ip)
            ->first();

        if ($existing) {
            if ($existing->tipo === $tipo) {
                $existing->delete();
                $active = null;
            } else {
                $existing->update(['tipo' => $tipo]);
                $active = $tipo;
            }
        } else {
            ReaccionNoticia::create([
                'noticia_id' => $noticia->id,
                'tipo' => $tipo,
                'ip_address' => $ip,
            ]);
            $active = $tipo;
        }

        return response()->json([
            'success' => true,
            'active' => $active,
            'counts' => $noticia->fresh()->reacciones_counts,
        ]);
    }
}
