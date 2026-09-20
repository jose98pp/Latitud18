<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Columnista extends Model
{
    use HasFactory;

    protected $table = 'columnistas';

    protected $fillable = [
        'nombre',
        'cargo',
        'avatar',
        'bio',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function articulos()
    {
        return $this->hasMany(ArticuloOpinion::class, 'columnista_id')->orderBy('created_at', 'desc');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true)->orderBy('orden', 'asc');
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }
        if ($this->avatar && (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://'))) {
            return $this->avatar;
        }
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nombre) . '&background=0B1F3A&color=fff&size=150';
    }
}
