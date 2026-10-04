<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PeriodicoPlantilla extends Model
{
    protected $table = 'periodico_plantillas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nombre',
        'slug',
        'categoria',
        'descripcion',
        'preview_color',
        'is_custom',
        'frames',
        'configuracion',
    ];

    protected $casts = [
        'frames' => 'array',
        'configuracion' => 'array',
        'is_custom' => 'boolean',
    ];

    public function ediciones(): HasMany
    {
        return $this->hasMany(PeriodicoEdicion::class, 'plantilla_id');
    }

    public function paginas(): HasMany
    {
        return $this->hasMany(PeriodicoPagina::class, 'plantilla_id');
    }

    /**
     * Crea una nueva edición a partir de esta plantilla sin alterar la plantilla original.
     */
    public function crearEdicion(array $atributos = []): PeriodicoEdicion
    {
        $edicionId = 'ed-' . time() . '-' . Str::random(5);

        $edicion = PeriodicoEdicion::create([
            'id' => $edicionId,
            'numero_edicion' => $atributos['numero_edicion'] ?? 'Edición ' . rand(100, 999),
            'fecha' => $atributos['fecha'] ?? date('d \d\e F \d\e Y'),
            'titulo' => $atributos['titulo'] ?? 'Latitud 18 — ' . ($atributos['numero_edicion'] ?? 'Nueva Edición'),
            'subtitulo' => $atributos['subtitulo'] ?? 'Información Sin Ruido',
            'slogan' => $atributos['slogan'] ?? 'El Periódico Digital de Santa Cruz',
            'ciudad' => $atributos['ciudad'] ?? 'Santa Cruz de la Sierra',
            'precio' => $atributos['precio'] ?? 'Bs 7,00',
            'num_paginas' => 1,
            'publicada' => false,
            'activa' => false,
            'estado' => 'borrador',
            'plantilla_id' => $this->id,
        ]);

        // Crear la primera página basada en la plantilla
        $pagina = $edicion->paginas()->create([
            'plantilla_id' => $this->id,
            'numero' => 1,
            'nombre' => 'Página 1: ' . $this->nombre,
            'seccion' => ucfirst($this->categoria ?? 'General'),
            'ancho' => 720,
            'alto' => 1040,
            'fondo_color' => '#ffffff',
            'configuracion' => $this->configuracion,
        ]);

        // Instanciar los elementos / frames de la plantilla
        $frames = is_array($this->frames) ? $this->frames : (json_decode($this->frames, true) ?: []);
        foreach ($frames as $idx => $f) {
            $pagina->crearElementoDesdeFrame($f, $idx);
        }

        return $edicion->load('paginas.elementos');
    }

    /**
     * Aplica los frames de esta plantilla a una página existente sin alterar la plantilla original.
     */
    public function aplicarAPagina(PeriodicoPagina $pagina): void
    {
        // Limpiar elementos existentes de la página
        $pagina->elementos()->delete();

        // Actualizar referencia de plantilla y sección
        $pagina->update([
            'plantilla_id' => $this->id,
            'seccion' => ucfirst($this->categoria ?? 'General'),
        ]);

        // Clonar los frames
        $frames = is_array($this->frames) ? $this->frames : (json_decode($this->frames, true) ?: []);
        foreach ($frames as $idx => $f) {
            $pagina->crearElementoDesdeFrame($f, $idx);
        }
    }
}
