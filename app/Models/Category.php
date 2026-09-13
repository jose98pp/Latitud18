<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'descripcion'];

    public function noticias()
    {
        return $this->hasMany(Noticia::class, 'category_id'); // Ajusta si el nombre del campo es diferente
    }


}
