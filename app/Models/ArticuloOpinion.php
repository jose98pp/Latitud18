<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloOpinion extends Model
{
    use HasFactory;

    protected $table = 'articulos_opinion';

    protected $fillable = [
        'columnista_id',
        'tipo',
        'titulo',
        'contenido',
        'publicado',
        'vistas',
    ];

    protected $casts = [
        'publicado' => 'boolean',
        'vistas' => 'integer',
    ];

    public function columnista()
    {
        return $this->belongsTo(Columnista::class, 'columnista_id');
    }

    public function scopePublicado($query)
    {
        return $query->where('publicado', true)->orderBy('created_at', 'desc');
    }
}
