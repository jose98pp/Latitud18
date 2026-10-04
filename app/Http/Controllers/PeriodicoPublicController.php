<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Banner;
use App\Models\PeriodicoEdicion;
use App\Services\PeriodicoPdfService;

class PeriodicoPublicController extends Controller
{
    public function __construct(private PeriodicoPdfService $pdfService)
    {
    }

    /**
     * Muestra la edición semanal activa en el visor público interactivo 3D / Flipbook
     */
    public function index()
    {
        // 1. Auto-publicar ediciones programadas cuya fecha ya ha llegado
        $now = now();
        $programadas = PeriodicoEdicion::where('estado', 'programado')
            ->whereNotNull('fecha_programada')
            ->where('fecha_programada', '<=', $now)
            ->get();

        foreach ($programadas as $p) {
            PeriodicoEdicion::where('activa', true)->update(['activa' => false]);
            $p->update([
                'estado' => 'publicado',
                'publicada' => true,
                'activa' => true,
                'fecha_publicacion' => $now,
            ]);
        }

        // 2. Obtener edición activa
        $edicionModel = PeriodicoEdicion::with(['paginas.elementos'])
            ->where('activa', true)
            ->where('publicada', true)
            ->first();

        // Si no hay activa pero hay publicadas, tomar la última publicada
        if (!$edicionModel) {
            $edicionModel = PeriodicoEdicion::with(['paginas.elementos'])
                ->where('publicada', true)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Si ninguna está publicada, tomar la última existente
        if (!$edicionModel) {
            $edicionModel = PeriodicoEdicion::with(['paginas.elementos'])
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $edicionActiva = $edicionModel ? $edicionModel->toEditorArray() : null;

        // 3. Listado de ediciones disponibles para el selector público
        $ediciones = PeriodicoEdicion::with(['paginas.elementos'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($e) => $e->toEditorArray())
            ->toArray();

        try {
            $categorias = Category::all();
            $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $banners = collect([]);
        }

        return view('periodico.reader', compact('edicionActiva', 'ediciones', 'categorias', 'banners'));
    }

    /**
     * Muestra una edición específica por ID en el visor interactivo
     */
    public function show($id)
    {
        $edicionModel = PeriodicoEdicion::with(['paginas.elementos'])->find($id);

        if (!$edicionModel) {
            return redirect()->route('periodico.public.index')->with('error', 'Edición no encontrada.');
        }

        $edicionActiva = $edicionModel->toEditorArray();

        $ediciones = PeriodicoEdicion::with(['paginas.elementos'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($e) => $e->toEditorArray())
            ->toArray();

        $categorias = Category::all();
        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');

        return view('periodico.reader', compact('edicionActiva', 'ediciones', 'categorias', 'banners'));
    }

    /**
     * Genera y descarga el PDF real de la edición (motor DomPDF server-side)
     */
    public function pdf($id)
    {
        $edicionModel = PeriodicoEdicion::with(['paginas.elementos'])->find($id);

        if (!$edicionModel) {
            return redirect()->route('periodico.public.index')->with('error', 'Edición no encontrada.');
        }

        try {
            return $this->pdfService->descarga($edicionModel->toEditorArray());
        } catch (\Throwable $e) {
            \Log::error('PeriodicoPublicController@pdf: ' . $e->getMessage(), [
                'edicion' => $id,
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('periodico.public.index')
                ->with('error', 'No se pudo generar el PDF en este momento. Intente nuevamente.');
        }
    }
}
