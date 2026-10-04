<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodicoElemento extends Model
{
    protected $table = 'periodico_elementos';

    protected $fillable = [
        'pagina_id',
        'frame_id',
        'tipo',
        'x',
        'y',
        'w',
        'h',
        'z',
        'contenido',
        'propiedades',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'w' => 'integer',
        'h' => 'integer',
        'z' => 'integer',
        'propiedades' => 'array',
    ];

    public function pagina(): BelongsTo
    {
        return $this->belongsTo(PeriodicoPagina::class, 'pagina_id');
    }

    /**
     * Convierte el modelo al objeto frame esperado por el editor visual InDesign
     */
    public function toFrameArray(): array
    {
        $props = is_array($this->propiedades) ? $this->propiedades : [];

        $frame = array_merge($props, [
            'id' => $this->frame_id,
            'type' => $this->tipo,
            'x' => $this->x,
            'y' => $this->y,
            'w' => $this->w,
            'h' => $this->h,
            'z' => $this->z,
        ]);

        if ($this->contenido !== null) {
            $frame['content'] = $this->contenido;
        }

        return $frame;
    }
}
