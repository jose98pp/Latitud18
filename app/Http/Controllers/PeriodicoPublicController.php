<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Banner;
use App\Http\Controllers\Admin\PeriodicoController;

class PeriodicoPublicController extends Controller
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/periodicos.json');
    }

    /**
     * Muestra la edición semanal activa en el visor público interactivo
     */
    public function index()
    {
        $adminController = new PeriodicoController();
        $ediciones = $adminController->getAllEdiciones();

        if (empty($ediciones)) {
            $defaultEdicion = $adminController->getDefaultEdicionStructure();
            $ediciones = [$defaultEdicion];
            $adminController->saveAllEdiciones($ediciones);
        }

        // Auto-publicar ediciones programadas cuya fecha ya ha llegado
        $modified = false;
        $now = now();

        foreach ($ediciones as &$ed) {
            if (($ed['estado'] ?? '') === 'programado' && !empty($ed['fecha_programada'])) {
                $schedTime = strtotime($ed['fecha_programada']);
                if ($schedTime && $schedTime <= $now->timestamp) {
                    $ed['estado'] = 'publicado';
                    $ed['publicada'] = true;
                    $ed['activa'] = true;
                    $ed['fecha_publicacion'] = $now->toISOString();
                    $modified = true;
                }
            }
        }
        unset($ed);

        if ($modified) {
            $activeFound = false;
            foreach ($ediciones as &$ed) {
                if (!empty($ed['activa']) && !empty($ed['publicada'])) {
                    if ($activeFound) {
                        $ed['activa'] = false;
                    } else {
                        $activeFound = true;
                    }
                }
            }
            unset($ed);
            $adminController->saveAllEdiciones($ediciones);
        }

        $edicionActiva = null;

        // Buscar la que esté marcada como activa
        foreach ($ediciones as $ed) {
            if (!empty($ed['activa']) && !empty($ed['publicada'])) {
                $edicionActiva = $ed;
                break;
            }
        }

        // Si no hay activa pero hay publicadas, tomar la primera publicada
        if (!$edicionActiva) {
            foreach ($ediciones as $ed) {
                if (!empty($ed['publicada'])) {
                    $edicionActiva = $ed;
                    break;
                }
            }
        }

        // Si ninguna está publicada, tomar la primera existente
        if (!$edicionActiva && !empty($ediciones)) {
            $edicionActiva = $ediciones[0];
        }

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
     * Muestra una edición específica por ID
     */
    public function show($id)
    {
        $adminController = new PeriodicoController();
        $ediciones = $adminController->getAllEdiciones();
        $edicionActiva = null;

        foreach ($ediciones as $ed) {
            if ($ed['id'] === $id) {
                $edicionActiva = $ed;
                break;
            }
        }

        if (!$edicionActiva) {
            return redirect()->route('periodico.public.index')->with('error', 'Edición no encontrada.');
        }

        $categorias = Category::all();
        $banners = Banner::where('active', true)->orderBy('position')->get()->groupBy('location');

        return view('periodico.reader', compact('edicionActiva', 'ediciones', 'categorias', 'banners'));
    }

    /**
     * Descarga / previsualización de PDF de una edición
     */
    public function pdf($id)
    {
        $adminController = new PeriodicoController();
        $ediciones = $adminController->getAllEdiciones();
        $edicion = null;

        foreach ($ediciones as $ed) {
            if ($ed['id'] === $id) {
                $edicion = $ed;
                break;
            }
        }

        if (!$edicion) {
            return redirect()->route('periodico.public.index')->with('error', 'Edición no encontrada.');
        }

        return view('admin.periodico.pdf-preview', compact('edicion'));
    }
}
