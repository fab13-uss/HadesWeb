<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'username',
        'email',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // -------------------------------------------------------------------------

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function initials(): string
    {
        return Str::of($this->nombre . ' ' . $this->apellido)
            ->explode(' ')
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->take(2)
            ->implode('');
    }

    public function esTecnico(): bool
    {
        return $this->rol === 'tecnico';
    }

    public function esProfesor(): bool
    {
        return $this->rol === 'profesor';
    }

    public function estaActivo(): bool
    {
        return $this->activo;
    }
}
