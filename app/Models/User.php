<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'last_login_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Obtener la URL del avatar
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        // Generar avatar por defecto usando las iniciales
        $initials = strtoupper(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&background=7c3aed&color=ffffff&size=128";
    }

    /**
     * Actualizar último login
     */
    public function updateLastLogin()
    {
        try {
            if (Schema::hasColumn('users', 'last_login_at')) {
                $this->update(['last_login_at' => now()]);
            }
        } catch (\Exception $e) {
            // Do nothing if column doesn't exist
        }
    }

    /**
     * Relación con noticias
     */
    public function noticias()
    {
        return $this->hasMany(Noticia::class, 'user_id');
    }
}
