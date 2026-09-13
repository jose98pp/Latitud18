<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaccionNoticia extends Model
{
    protected $table = 'reacciones_noticias';

    protected $fillable = [
        'noticia_id',
        'tipo',
        'ip_address',
    ];

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class, 'noticia_id');
    }
}
