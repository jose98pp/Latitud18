<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'descripcion'];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->name) ?: (string) $this->id;
    }

    public function getUrlAttribute(): string
    {
        return route('categoria.noticias', $this->slug);
    }

    public function noticias()
    {
        return $this->hasMany(Noticia::class, 'category_id');
    }
}
