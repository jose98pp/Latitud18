<?php

namespace App\Services;

use App\Models\PeriodicoPagina;
use App\Models\PeriodicoPlantilla;
use App\Services\Contracts\PlantillaServiceInterface;

class PlantillaService implements PlantillaServiceInterface
{
    /**
     * Catálogo oficial de formatos publicitarios LATITUD 18.
     * Dimensiones en píxeles (96 dpi) y centímetros.
     *
     * @see Requirements 3.1
     */
    public const AD_FORMATS = [
        'A1' => [
            'label' => 'Banner Superior',
            'w_px'  => 932,
            'h_px'  => 227,
            'w_cm'  => 24.7,
            'h_cm'  => 6.0,
        ],
        'A2' => [
            'label' => 'Banner Interior',
            'w_px'  => 932,
            'h_px'  => 227,
            'w_cm'  => 24.7,
            'h_cm'  => 6.0,
        ],
        'B1' => [
            'label' => 'Media Página Vertical',
            'w_px'  => 499,
            'h_px'  => 983,
            'w_cm'  => 13.2,
            'h_cm'  => 26.0,
        ],
        'B2' => [
            'label' => 'Media Página Vertical',
            'w_px'  => 499,
            'h_px'  => 983,
            'w_cm'  => 13.2,
            'h_cm'  => 26.0,
        ],
        'C1' => [
            'label' => 'Media Página Horizontal',
            'w_px'  => 1006,
            'h_px'  => 484,
            'w_cm'  => 26.6,
            'h_cm'  => 12.8,
        ],
        'C2' => [
            'label' => 'Media Página Horizontal',
            'w_px'  => 1006,
            'h_px'  => 484,
            'w_cm'  => 26.6,
            'h_cm'  => 12.8,
        ],
        'D1' => [
            'label' => '1/4 Página Vertical',
            'w_px'  => 242,
            'h_px'  => 492,
            'w_cm'  => 6.4,
            'h_cm'  => 13.0,
        ],
        'D2' => [
            'label' => '1/4 Página Vertical',
            'w_px'  => 242,
            'h_px'  => 492,
            'w_cm'  => 6.4,
            'h_cm'  => 13.0,
        ],
        'E1' => [
            'label' => '1/4 Página Horizontal',
            'w_px'  => 499,
            'h_px'  => 235,
            'w_cm'  => 13.2,
            'h_cm'  => 6.2,
        ],
        'E2' => [
            'label' => '1/4 Página Horizontal',
            'w_px'  => 499,
            'h_px'  => 235,
            'w_cm'  => 13.2,
            'h_cm'  => 6.2,
        ],
        'E3' => [
            'label' => '1/4 Página Horizontal',
            'w_px'  => 499,
            'h_px'  => 235,
            'w_cm'  => 13.2,
            'h_cm'  => 6.2,
        ],
        'E4' => [
            'label' => '1/4 Página Horizontal',
            'w_px'  => 499,
            'h_px'  => 235,
            'w_cm'  => 13.2,
            'h_cm'  => 6.2,
        ],
        'F1' => [
            'label' => 'Pie de Página',
            'w_px'  => 1006,
            'h_px'  => 182,
            'w_cm'  => 26.6,
            'h_cm'  => 4.8,
        ],
        'F2' => [
            'label' => 'Pie de Página',
            'w_px'  => 1006,
            'h_px'  => 182,
            'w_cm'  => 26.6,
            'h_cm'  => 4.8,
        ],
    ];

    /**
     * Aplica una plantilla a una página existente (deep copy de frames).
     * Elimina los elementos actuales de la página, actualiza la referencia de plantilla
     * y crea nuevos PeriodicoElemento a partir de los frames de la plantilla.
     * No modifica ningún campo del registro $plantilla original.
     *
     * @see Requirements 1.2, 1.4
     */
    public function aplicarAPagina(PeriodicoPlantilla $plantilla, PeriodicoPagina $pagina): void
    {
        // 1. Eliminar todos los elementos existentes de la página
        $pagina->elementos()->delete();

        // 2. Actualizar referencia de plantilla y sección en la página
        $pagina->update([
            'plantilla_id' => $plantilla->id,
            'seccion'      => ucfirst($plantilla->categoria ?? 'general'),
        ]);

        // 3. Obtener los frames de la plantilla como array PHP
        $frames = is_array($plantilla->frames)
            ? $plantilla->frames
            : (json_decode($plantilla->frames, true) ?: []);

        // 4. Crear un PeriodicoElemento por cada frame (deep copy implícita — $plantilla no se toca)
        foreach ($frames as $idx => $f) {
            $pagina->crearElementoDesdeFrame($f, $idx);
        }
    }

    /**
     * Genera un slug URL-friendly único a partir de un nombre de plantilla.
     * Lowercasea, reemplaza espacios con guiones y elimina caracteres no alfanuméricos.
     * Si el slug generado ya existe en periodico_plantillas, agrega sufijo -2, -3, etc.
     * El parámetro $excludeId excluye el propio registro al actualizar una plantilla existente.
     *
     * @see Requirements 1.6
     */
    public function generarSlugUnico(string $nombre, ?string $excludeId = null): string
    {
        // 1. Normalise the input name
        $slug = strtolower($nombre);
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^a-z0-9-]+/', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug);

        // 2. Check uniqueness and append numeric suffix when needed
        $candidate = $slug;
        $suffix    = 2;
        $maxTries  = 100;

        while ($suffix <= $maxTries + 1) {
            $query = PeriodicoPlantilla::where('slug', $candidate);

            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                return $candidate;
            }

            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        // Fallback: append a short random string if all 100 numeric suffixes are taken
        return $slug . '-' . substr(uniqid(), -6);
    }

    /**
     * Crea una copia independiente de la plantilla con is_custom = true
     * y nombre con sufijo " (Copia)". Los frames se clonan con deep copy para
     * que modificar la copia no afecte la plantilla original.
     *
     * @see Requirements 10.2
     */
    public function duplicar(PeriodicoPlantilla $plantilla): PeriodicoPlantilla
    {
        $nuevoId = 'tpl-' . \Illuminate\Support\Str::random(12);

        // Deep copy via json round-trip para garantizar independencia total
        $framesCopia        = json_decode(json_encode($plantilla->frames ?? []), true);
        $configuracionCopia = json_decode(json_encode($plantilla->configuracion ?? []), true);

        $slug = $this->generarSlugUnico($plantilla->nombre . ' Copia');

        return PeriodicoPlantilla::create([
            'id'            => $nuevoId,
            'nombre'        => $plantilla->nombre . ' (Copia)',
            'slug'          => $slug,
            'categoria'     => $plantilla->categoria,
            'descripcion'   => $plantilla->descripcion,
            'preview_color' => $plantilla->preview_color,
            'is_custom'     => true,
            'frames'        => $framesCopia,
            'configuracion' => $configuracionCopia,
        ]);
    }

    /**
     * Exporta la plantilla como array listo para serializar a JSON .latitud-template.
     * Incluye los campos: id, name, category, description, preview_color, frames.
     *
     * @see Requirements 10.3
     */
    public function exportar(PeriodicoPlantilla $plantilla): array
    {
        return [
            'id'            => $plantilla->id,
            'name'          => $plantilla->nombre,
            'category'      => $plantilla->categoria,
            'description'   => $plantilla->descripcion,
            'preview_color' => $plantilla->preview_color,
            'frames'        => is_array($plantilla->frames) ? $plantilla->frames : [],
        ];
    }

    /**
     * Importa una plantilla desde un array validado y persiste un nuevo registro
     * con is_custom = true. Valida que el array contenga la clave 'frames' y que
     * 'frames' sea un array no vacío.
     *
     * @see Requirements 10.4, 10.5
     *
     * @throws \InvalidArgumentException si la validación de $data falla.
     */
    public function importar(array $data): PeriodicoPlantilla
    {
        // Validate presence of 'frames' key
        if (!isset($data['frames'])) {
            throw new \InvalidArgumentException("Missing required 'frames' key");
        }

        // Validate that 'frames' is an array
        if (!is_array($data['frames'])) {
            throw new \InvalidArgumentException("'frames' must be an array");
        }

        // Validate that 'frames' is not empty
        if (count($data['frames']) === 0) {
            throw new \InvalidArgumentException("'frames' array must not be empty");
        }

        $nombre = $data['name'] ?? 'Plantilla Importada';

        return PeriodicoPlantilla::create([
            'id'            => 'tpl-' . \Illuminate\Support\Str::random(12),
            'nombre'        => $nombre,
            'slug'          => $this->generarSlugUnico($data['name'] ?? 'plantilla-importada'),
            'categoria'     => $data['category'] ?? 'general',
            'descripcion'   => $data['description'] ?? null,
            'preview_color' => $data['preview_color'] ?? '#cccccc',
            'is_custom'     => true,
            'frames'        => $data['frames'],
            'configuracion' => $data['configuracion'] ?? [
                'columns'          => 5,
                'column_width_px'  => 144,
                'gutter_px'        => 4,
                'margin_px'        => 12,
                'page_width_px'    => 720,
                'page_height_px'   => 1040,
            ],
        ]);
    }
}
