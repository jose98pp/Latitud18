<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comentario extends Model
{
    protected $table = 'comentarios';

    protected $fillable = [
        'noticia_id',
        'nombre',
        'email',
        'contenido',
        'aprobado',
        'ip_address',
    ];

    protected $casts = [
        'aprobado' => 'boolean',
    ];

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class, 'noticia_id');
    }

    public function scopeAprobados($query)
    {
        return $query->where('aprobado', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('aprobado', false);
    }
}
