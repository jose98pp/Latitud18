<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'link',
        'location',
        'active',
        'position'
    ];

    protected $casts = [
        'active' => 'boolean',
        'position' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}
