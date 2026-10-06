<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodicoPlantillaEdicion;
use App\Services\Contracts\EdicionServiceInterface;
use Illuminate\Http\Request;

/**
 * Gestiona las Plantillas de Edición Completa (12 páginas).
 * Permite crear, listar y generar nuevas ediciones a partir de un mapping
 * de 12 slots con plantillas asignadas.
 *
 * @see Requirements 5.1, 5.4, 5.5, 5.7, 5.8
 */
class PeriodicoPlantillaEdicionController extends Controller
{
    public function __construct(
        protected EdicionServiceInterface $edicionService
    ) {}

    /**
     * Lista todas las plantillas de edición disponibles.
     */
    public function index()
    {
        $plantillasEdicion = PeriodicoPlantillaEdicion::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'plantillas_edicion' => $plantillasEdicion,
        ]);
    }

    /**
     * Guarda una nueva plantilla de edición (mapping de 12 slots).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'mapping'     => 'required|array|size:12',
            'mapping.*.slot'          => 'required|integer|min:1|max:12',
            'mapping.*.plantilla_id'  => 'nullable|string',
        ]);

        $plantillaEdicion = PeriodicoPlantillaEdicion::create([
            'nombre'      => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'mapping'     => $validated['mapping'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plantilla de edición "' . $plantillaEdicion->nombre . '" guardada exitosamente.',
            'plantilla_edicion' => $plantillaEdicion,
        ], 201);
    }

    /**
     * Crea una nueva edición de 12 páginas a partir de una plantilla de edición.
     */
    public function crearEdicion(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_edicion' => 'required|string|max:100',
            'fecha'          => 'required|string|max:100',
            'titulo'         => 'nullable|string|max:255',
        ]);

        $plantillaEdicion = PeriodicoPlantillaEdicion::find($id);
        if (!$plantillaEdicion) {
            return response()->json([
                'success' => false,
                'message' => 'Plantilla de edición no encontrada.',
            ], 404);
        }

        try {
            $edicion = $this->edicionService->crearDesdeMapping(
                $plantillaEdicion->mapping,
                [
                    'numero_edicion' => $validated['numero_edicion'],
                    'fecha'          => $validated['fecha'],
                    'titulo'         => $validated['titulo'] ?? 'Latitud 18 — ' . $validated['numero_edicion'],
                ]
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Edición de 12 páginas creada exitosamente.',
                'edicion'      => $edicion->toEditorArray(),
                'redirect_url' => route('admin.periodico.index', ['edicion_id' => $edicion->id]),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error creating edition from template', [
                'plantilla_edicion_id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Extract failed slot number from RuntimeException message if available
            $failedSlot = null;
            if (preg_match('/página (\d+)/', $e->getMessage(), $m)) {
                $failedSlot = (int) $m[1];
            }

            return response()->json([
                'success'     => false,
                'message'     => 'Error al crear la edición: ' . $e->getMessage(),
                'failed_slot' => $failedSlot,
            ], 500);
        }
    }
}
