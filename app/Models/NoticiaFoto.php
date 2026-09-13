<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NoticiaFoto extends Model
{
    protected $table = 'noticia_fotos';

    protected $fillable = [
        'noticia_id',
        'image_path',
        'pie_de_foto',
        'orden',
    ];

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class, 'noticia_id');
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return asset('images/default-news.svg');
        }

        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'storage/')) {
            return asset($this->image_path);
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
