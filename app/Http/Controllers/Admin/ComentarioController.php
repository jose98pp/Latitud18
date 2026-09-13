<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comentario;

class ComentarioController extends Controller
{
    /**
     * Muestra el panel de moderación de comentarios
     */
    public function index(Request $request)
    {
        $query = Comentario::with('noticia');

        // Filtro por estado
        if ($request->has('estado')) {
            if ($request->estado === 'aprobados') {
                $query->where('aprobado', true);
            } elseif ($request->estado === 'pendientes') {
                $query->where('aprobado', false);
            }
        }

        // Búsqueda por texto o autor
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('contenido', 'like', $term);
            });
        }

        $comentarios = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalAprobados = Comentario::where('aprobado', true)->count();
        $totalPendientes = Comentario::where('aprobado', false)->count();

        return view('admin.comentarios.index', compact('comentarios', 'totalAprobados', 'totalPendientes'));
    }

    /**
     * Alterna el estado de aprobación de un comentario
     */
    public function toggleAprobado($id)
    {
        $comentario = Comentario::findOrFail($id);
        $comentario->aprobado = !$comentario->aprobado;
        $comentario->save();

        $mensaje = $comentario->aprobado ? 'Comentario aprobado y visible públicamente.' : 'Comentario ocultado.';

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Elimina un comentario
     */
    public function destroy($id)
    {
        $comentario = Comentario::findOrFail($id);
        $comentario->delete();

        return redirect()->back()->with('success', 'Comentario eliminado exitosamente.');
    }
}
