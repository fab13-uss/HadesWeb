<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
