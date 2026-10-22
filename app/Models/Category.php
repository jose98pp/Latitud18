<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'descripcion'];

    public function getEsDeportivaAttribute(): bool
    {
        return Str::contains(Str::lower(Str::ascii($this->name ?? '')), ['deport', 'futbol', 'contra ataque', 'contraataque']);
    }

    public function scopeDeportivas($query)
    {
        return $query->where(function ($q) {
            $q->where('name', 'LIKE', '%deport%')->orWhere('name', 'LIKE', '%futbol%')
                ->orWhere('name', 'LIKE', '%fútbol%')->orWhere('name', 'LIKE', '%contra ataque%')->orWhere('name', 'LIKE', '%contraataque%');
        });
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->name) ?: (string) $this->id;
    }

    public function getUrlAttribute(): string
    {
        return $this->es_deportiva ? route('contraataque.index') : route('categoria.noticias', $this->slug);
    }

    public function noticias()
    {
        return $this->hasMany(Noticia::class, 'category_id');
    }
}
