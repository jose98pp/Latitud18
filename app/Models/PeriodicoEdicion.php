<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PeriodicoEdicion extends Model
{
    protected $table = 'periodico_ediciones';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'numero_edicion',
        'fecha',
        'titulo',
        'subtitulo',
        'slogan',
        'ciudad',
        'precio',
        'num_paginas',
        'publicada',
        'activa',
        'estado',
        'fecha_programada',
        'fecha_publicacion',
        'pdf_url',
        'plantilla_id',
    ];

    protected $casts = [
        'num_paginas' => 'integer',
        'publicada' => 'boolean',
        'activa' => 'boolean',
        'fecha_programada' => 'datetime',
        'fecha_publicacion' => 'datetime',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PeriodicoPlantilla::class, 'plantilla_id');
    }

    public function paginas(): HasMany
    {
        return $this->hasMany(PeriodicoPagina::class, 'edicion_id')->orderBy('numero', 'asc');
    }

    public function elementos(): HasManyThrough
    {
        return $this->hasManyThrough(PeriodicoElemento::class, PeriodicoPagina::class, 'edicion_id', 'pagina_id');
    }

    public function scopePublicadas($query)
    {
        return $query->where('publicada', true);
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true)->where('publicada', true);
    }

    /**
     * Devuelve la estructura completa de la edición en formato compatible con el editor visual y visor 3D
     */
    public function toEditorArray(): array
    {
        $this->loadMissing(['paginas.elementos']);

        return [
            'id' => $this->id,
            'numero_edicion' => $this->numero_edicion,
            'fecha' => $this->fecha,
            'titulo' => $this->titulo,
            'subtitulo' => $this->subtitulo,
            'slogan' => $this->slogan,
            'ciudad' => $this->ciudad,
            'precio' => $this->precio,
            'num_paginas' => $this->paginas->count(),
            'publicada' => (bool)$this->publicada,
            'activa' => (bool)$this->activa,
            'estado' => $this->estado ?? ($this->publicada ? 'publicado' : 'borrador'),
            'fecha_programada' => $this->fecha_programada ? $this->fecha_programada->toISOString() : null,
            'fecha_publicacion' => $this->fecha_publicacion ? $this->fecha_publicacion->toISOString() : null,
            'pdf_url' => $this->pdf_url,
            'plantilla_id' => $this->plantilla_id,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : now()->toISOString(),
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : now()->toISOString(),
            'paginas' => $this->paginas->map(fn($p) => $p->toEditorPageArray())->values()->toArray(),
        ];
    }

    /**
     * Sincroniza y guarda los cambios enviados desde el editor visual
     */
    public function syncFromEditorData(array $data): void
    {
        // Actualizar campos base
        $campos = ['numero_edicion', 'fecha', 'titulo', 'subtitulo', 'slogan', 'ciudad', 'precio', 'estado', 'pdf_url'];
        foreach ($campos as $c) {
            if (array_key_exists($c, $data)) {
                $this->{$c} = $data[$c];
            }
        }

        if (array_key_exists('publicada', $data)) {
            $this->publicada = (bool)$data['publicada'];
        }
        if (array_key_exists('activa', $data)) {
            $this->activa = (bool)$data['activa'];
        }
        if (array_key_exists('fecha_programada', $data)) {
            $this->fecha_programada = !empty($data['fecha_programada']) ? $data['fecha_programada'] : null;
        }

        // Actualizar páginas si vienen en el payload
        if (isset($data['paginas'])) {
            $paginasData = is_string($data['paginas']) ? json_decode($data['paginas'], true) : $data['paginas'];

            if (is_array($paginasData)) {
                // Eliminar páginas que ya no existen
                $existingPageIds = array_filter(array_column($paginasData, 'id'));
                if (!empty($existingPageIds)) {
                    $this->paginas()->whereNotIn('id', $existingPageIds)->delete();
                } else {
                    $this->paginas()->delete();
                }

                foreach ($paginasData as $pIdx => $pData) {
                    $pNumero = $pData['numero'] ?? ($pIdx + 1);
                    $pNombre = $pData['nombre'] ?? ('Página ' . $pNumero);
                    $pSeccion = $pData['seccion'] ?? null;
                    $pAncho = (int)($pData['ancho'] ?? 720);
                    $pAlto = (int)($pData['alto'] ?? 1040);
                    $pFondo = $pData['fondo_color'] ?? '#ffffff';
                    $pConfig = $pData['configuracion'] ?? null;
                    $pPlantillaId = $pData['plantilla_id'] ?? null;

                    $pagina = null;
                    if (!empty($pData['id'])) {
                        $pagina = $this->paginas()->where('id', $pData['id'])->first();
                    }

                    if ($pagina) {
                        $pagina->update([
                            'numero' => $pNumero,
                            'nombre' => $pNombre,
                            'seccion' => $pSeccion,
                            'ancho' => $pAncho,
                            'alto' => $pAlto,
                            'fondo_color' => $pFondo,
                            'configuracion' => $pConfig,
                        ]);
                    } else {
                        $pagina = $this->paginas()->create([
                            'plantilla_id' => $pPlantillaId,
                            'numero' => $pNumero,
                            'nombre' => $pNombre,
                            'seccion' => $pSeccion,
                            'ancho' => $pAncho,
                            'alto' => $pAlto,
                            'fondo_color' => $pFondo,
                            'configuracion' => $pConfig,
                        ]);
                    }

                    if (isset($pData['frames']) && is_array($pData['frames'])) {
                        $pagina->syncElementos($pData['frames']);
                    }
                }

                $this->num_paginas = count($paginasData);
            }
        }

        $this->save();
    }
}
