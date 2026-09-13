<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;

class Noticia extends Model
{
    use HasFactory, HasImages;

    protected $fillable = [
        'titulo',
        'contenido',
        'category_id',
        'user_id',
        'publicada',
        'destacada_hero',
        'es_investigacion',
        'es_urgente',
        'urgente_hasta',
        'publicar_en',
        'video_youtube',
        'imagen',
        'views',
    ];

    protected $casts = [
        'publicada' => 'boolean',
        'destacada_hero' => 'boolean',
        'es_investigacion' => 'boolean',
        'es_urgente' => 'boolean',
        'urgente_hasta' => 'datetime',
        'publicar_en' => 'datetime',
        'views' => 'integer',
    ];

    public function scopePublicadaActiva($query)
    {
        return $query->where('publicada', true)
            ->where(function ($q) {
                $q->whereNull('publicar_en')
                  ->orWhere('publicar_en', '<=', now());
            });
    }

    public function getEstaProgramadaAttribute(): bool
    {
        return $this->publicada && $this->publicar_en && $this->publicar_en->isFuture();
    }

    public function scopeHero($query)
    {
        return $query->publicadaActiva()->where('destacada_hero', true);
    }

    public function scopeInvestigacion($query)
    {
        return $query->publicadaActiva()->where('es_investigacion', true);
    }

    public function scopeUrgente($query)
    {
        return $query->publicadaActiva()->where('es_urgente', true);
    }

    public function scopeUrgenteActivo($query)
    {
        return $query->publicadaActiva()
            ->where('es_urgente', true)
            ->where(function ($q) {
                $q->whereNull('urgente_hasta')
                  ->orWhere('urgente_hasta', '>=', now());
            });
    }

    public function getUrlAttribute(): string
    {
        $categorySlug = $this->category ? \Illuminate\Support\Str::slug($this->category->name) : 'general';
        $newsSlug = \Illuminate\Support\Str::slug($this->titulo) ?: 'noticia';
        return url("/{$categorySlug}/{$newsSlug}/{$this->id}");
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'noticia_id')->orderBy('created_at', 'desc');
    }

    public function comentariosAprobados()
    {
        return $this->hasMany(Comentario::class, 'noticia_id')->where('aprobado', true)->orderBy('created_at', 'desc');
    }

    public function galeria()
    {
        return $this->hasMany(NoticiaFoto::class, 'noticia_id')->orderBy('orden')->orderBy('created_at');
    }

    public function reacciones()
    {
        return $this->hasMany(ReaccionNoticia::class, 'noticia_id');
    }

    public function getReaccionesCountsAttribute(): array
    {
        $counts = $this->reacciones()
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        return [
            'me_informa' => $counts['me_informa'] ?? 0,
            'interesante' => $counts['interesante'] ?? 0,
            'me_indigna' => $counts['me_indigna'] ?? 0,
            'recomiendo' => $counts['recomiendo'] ?? 0,
            'total' => array_sum($counts),
        ];
    }

    public function getUserReactionAttribute(): ?string
    {
        $ip = request()->ip();
        if (!$ip) return null;
        return $this->reacciones()->where('ip_address', $ip)->value('tipo');
    }

    public function getYoutubeIdAttribute()
    {
        if (empty($this->video_youtube)) {
            return null;
        }
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $this->video_youtube, $match)) {
            return $match[1];
        }
        if (strlen(trim($this->video_youtube)) === 11) {
            return trim($this->video_youtube);
        }
        return null;
    }

    public function getYoutubeThumbnailAttribute()
    {
        $id = $this->youtube_id;
        if ($id) {
            return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
        }
        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
