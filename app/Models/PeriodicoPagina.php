<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PeriodicoPagina extends Model
{
    protected $table = 'periodico_paginas';

    protected $fillable = [
        'edicion_id',
        'plantilla_id',
        'numero',
        'nombre',
        'seccion',
        'ancho',
        'alto',
        'fondo_color',
        'configuracion',
    ];

    protected $casts = [
        'numero' => 'integer',
        'ancho' => 'integer',
        'alto' => 'integer',
        'configuracion' => 'array',
    ];

    public function edicion(): BelongsTo
    {
        return $this->belongsTo(PeriodicoEdicion::class, 'edicion_id');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PeriodicoPlantilla::class, 'plantilla_id');
    }

    public function elementos(): HasMany
    {
        return $this->hasMany(PeriodicoElemento::class, 'pagina_id')->orderBy('z', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Crea un elemento a partir de un objeto frame
     */
    public function crearElementoDesdeFrame(array $f, int $defaultZ = 1): PeriodicoElemento
    {
        $frameId = $f['id'] ?? ('f-' . time() . '-' . Str::random(5));
        $tipo = $f['type'] ?? 'article';
        $x = (int)($f['x'] ?? 20);
        $y = (int)($f['y'] ?? 20);
        $w = (int)($f['w'] ?? 300);
        $h = (int)($f['h'] ?? 150);
        $z = (int)($f['z'] ?? ($defaultZ + 1));
        $content = $f['content'] ?? null;

        // Extraer propiedades restantes no estándar
        $propiedades = $f;
        unset($propiedades['id'], $propiedades['type'], $propiedades['x'], $propiedades['y'], $propiedades['w'], $propiedades['h'], $propiedades['z'], $propiedades['content']);

        return $this->elementos()->create([
            'frame_id' => $frameId,
            'tipo' => $tipo,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h,
            'z' => $z,
            'contenido' => $content,
            'propiedades' => $propiedades,
        ]);
    }

    /**
     * Sincroniza la lista completa de frames de la página recibida desde el editor visual
     */
    public function syncElementos(array $frames): void
    {
        $this->elementos()->delete();

        foreach ($frames as $idx => $f) {
            $this->crearElementoDesdeFrame($f, $idx);
        }
    }

    /**
     * Convierte la página a la estructura esperada por el editor JS y el visor
     */
    public function toEditorPageArray(): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'seccion' => $this->seccion,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'fondo_color' => $this->fondo_color,
            'configuracion' => $this->configuracion,
            'frames' => $this->elementos->map(fn($el) => $el->toFrameArray())->values()->toArray(),
        ];
    }
}
