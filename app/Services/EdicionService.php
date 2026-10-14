<?php

namespace App\Services;

use App\Models\PeriodicoEdicion;
use App\Models\PeriodicoPlantilla;
use App\Services\Contracts\EdicionServiceInterface;
use App\Services\Contracts\PlantillaServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles the transactional creation of a complete 12/28-page edition.
 *
 * The main method `crearDesdeMapping()` wraps all database writes in
 * DB::transaction() so that any failure on any page slot triggers an
 * automatic rollback, leaving no partial PeriodicoEdicion persisted.
 *
 * @see Requirements 5.1, 5.2, 5.3, 5.6, 5.7, 5.8
 */
class EdicionService implements EdicionServiceInterface
{
    /** Validate a complete, consecutive 12/28-page mapping before any writes. */
    public static function numeroPaginasParaMapping(array $mapping): int
    {
        $count = count($mapping);
        $slots = array_map(fn ($entry) => (int) ($entry['slot'] ?? 0), $mapping);
        sort($slots);
        if (!in_array($count, [12, 28], true) || $slots !== range(1, $count)) {
            throw new \InvalidArgumentException('La edición debe tener 12 o 28 páginas consecutivas, sin páginas repetidas.');
        }
        return $count;
    }

    public function __construct(
        protected PlantillaServiceInterface $plantillaService
    ) {}

    /**
     * Creates a complete 12/28-page edition from a slot→plantilla mapping.
     *
     * Wraps all Edicion, PeriodicoPagina, and PeriodicoElemento inserts
     * inside a single DB::transaction(). Any failure on any page slot triggers
     * an automatic rollback, leaving no partial PeriodicoEdicion persisted.
     *
     * @param array $mapping  12 or 28 entries: [{"slot":1,"plantilla_id":"tpl-portada"|null}, ...]
     * @param array $atributos  Edition attributes: numero_edicion, fecha, titulo, etc.
     * @return PeriodicoEdicion  The created edition with pages and elements loaded
     *
     * @throws \RuntimeException  If any page slot fails during creation (rollback occurs automatically)
     *
     * @see Requirements 5.1, 5.2, 5.3, 5.6, 5.7, 5.8
     */
    public function crearDesdeMapping(array $mapping, array $atributos): PeriodicoEdicion
    {
        $numPaginas = self::numeroPaginasParaMapping($mapping);
        // Section name map: slot number → section name (Req 5.2)
        $secciones = [
            1  => 'Portada',
            2  => 'Editorial/Opinión',
            3  => 'Política',
            4  => 'Política',
            5  => 'Santa Cruz',
            6  => 'Santa Cruz',
            7  => 'País',
            8  => 'País',
            9  => 'Economía',
            10 => 'Economía',
            11 => 'Seguridad/Judicial',
            12 => 'Mundo/Deportes/Cultura',
        ];

        return DB::transaction(function () use ($mapping, $atributos, $secciones, $numPaginas) {
            // Build mapping index keyed by slot number
            $mappingIndex = [];
            foreach ($mapping as $entry) {
                $mappingIndex[(int)$entry['slot']] = $entry['plantilla_id'] ?? null;
            }

            // Create the edition record (Req 5.1, 5.3)
            $edicionId = 'ed-' . time() . '-' . Str::random(5);
            $edicion = PeriodicoEdicion::create([
                'id'             => $edicionId,
                'numero_edicion' => $atributos['numero_edicion'] ?? 'Edición ' . rand(100, 999),
                'fecha'          => $atributos['fecha'] ?? now()->format('d \d\e F \d\e Y'),
                'titulo'         => $atributos['titulo'] ?? 'Latitud 18',
                'subtitulo'      => $atributos['subtitulo'] ?? 'Información Sin Ruido',
                'slogan'         => $atributos['slogan'] ?? 'El Periódico Digital de Santa Cruz',
                'ciudad'         => $atributos['ciudad'] ?? 'Santa Cruz de la Sierra',
                'precio'         => $atributos['precio'] ?? 'Bs 7,00',
                'num_paginas'    => $numPaginas,
                'publicada'      => false,
                'activa'         => false,
                'estado'         => 'borrador',
                'plantilla_id'   => null,
            ]);

            // One page per slot; the applied template supplies its own section.
            for ($slot = 1; $slot <= $numPaginas; $slot++) {
                $seccion     = $secciones[$slot] ?? ($slot >= 17 ? 'Avisos / Edictos' : 'General');
                $plantillaId = $mappingIndex[$slot] ?? null;

                try {
                    $pagina = $edicion->paginas()->create([
                        'plantilla_id'  => $plantillaId,
                        'numero'        => $slot,
                        'nombre'        => 'Página ' . $slot . ': ' . $seccion,
                        'seccion'       => $seccion,
                        'ancho'         => 720,
                        'alto'          => 1040,
                        'fondo_color'   => '#ffffff',
                        'configuracion' => [
                            'columns'         => 5,
                            'column_width_px' => 144,
                            'gutter_px'       => 4,
                            'margin_px'       => 12,
                            'page_width_px'   => 720,
                            'page_height_px'  => 1040,
                        ],
                    ]);

                    if ($plantillaId) {
                        // Apply the assigned template — deep copies frames without touching $plantilla (Req 1.2)
                        $plantilla = PeriodicoPlantilla::find($plantillaId);
                        if ($plantilla) {
                            $this->plantillaService->aplicarAPagina($plantilla, $pagina);
                        }
                        // If plantilla_id was provided but not found, page remains blank (Req 1.7 fallback
                        // is handled at the controller layer; here the slot still produces a valid Pagina)
                    }
                    // If no plantilla assigned, page stays blank with zero elements (Req 5.6)

                } catch (\Throwable $e) {
                    // DB::transaction() will auto-rollback on re-throw (Req 5.8)
                    throw new \RuntimeException(
                        "Error al crear el slot {$slot} / página {$slot} ({$seccion}): " . $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }

            return $edicion->load('paginas.elementos');
        });
    }
}
