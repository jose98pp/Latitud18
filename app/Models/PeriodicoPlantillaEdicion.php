<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodicoPlantillaEdicion extends Model
{
    protected $table = 'periodico_plantillas_edicion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'mapping',
    ];

    protected $casts = [
        'mapping' => 'array',
    ];

    /**
     * Devuelve el plantilla_id asignado a un slot dado a partir del mapping JSON.
     *
     * El mapping es un array de entradas con la forma:
     *   [{"slot": 1, "plantilla_id": "tpl_portada_clasica"}, ..., {"slot": 12, "plantilla_id": null}]
     *
     * @param  int  $slot  Número de slot (1–12).
     * @return string|null  El plantilla_id del slot, o null si el slot no existe
     *                      o si su plantilla_id está explícitamente como null.
     */
    public function getSlotPlantillaId(int $slot): ?string
    {
        $mapping = is_array($this->mapping) ? $this->mapping : [];

        foreach ($mapping as $entry) {
            if (isset($entry['slot']) && (int) $entry['slot'] === $slot) {
                return isset($entry['plantilla_id']) ? ($entry['plantilla_id'] ?: null) : null;
            }
        }

        return null;
    }
}
