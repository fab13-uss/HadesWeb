<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GestionUsuarios extends Component
{
    // Modal crear/editar
    public bool   $modalAbierto  = false;
    public bool   $modoEdicion   = false;
    public ?int   $usuarioId     = null;

    // Campos del formulario
    public string $nombre   = '';
    public string $apellido = '';
    public string $username = '';
    public string $email    = '';
    public string $rol      = 'profesor';
    public string $password = '';

    // Búsqueda
    public string $busqueda = '';

    // =========================================================================

    #[Computed]
    public function usuarios()
    {
        return User::when($this->busqueda, function ($q) {
                $b = $this->busqueda;
                $q->where(fn ($q) =>
                    $q->where('nombre',   'ilike', "%{$b}%")
                      ->orWhere('apellido', 'ilike', "%{$b}%")
                      ->orWhere('username', 'ilike', "%{$b}%")
                );
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();
    }

    // =========================================================================
    // Crear usuario
    // =========================================================================

    public function abrirModalCrear(): void
    {
        $this->resetForm();
        $this->modoEdicion  = false;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'nullable|email|unique:users,email',
            'rol'      => 'required|in:tecnico,profesor',
            'password' => ['required', Password::min(8)],
        ]);

        User::create([
            'nombre'   => $this->nombre,
            'apellido' => $this->apellido,
            'username' => $this->username,
            'email'    => $this->email ?: null,
            'rol'      => $this->rol,
            'password' => Hash::make($this->password),
            'activo'   => true,
        ]);

        $this->modalAbierto = false;
        $this->resetForm();
        session()->flash('mensaje', 'Usuario creado correctamente.');
    }

    // =========================================================================
    // Editar usuario
    // =========================================================================

    public function abrirModalEditar(int $id): void
    {
        $usuario = User::findOrFail($id);

        $this->usuarioId = $id;
        $this->nombre    = $usuario->nombre;
        $this->apellido  = $usuario->apellido;
        $this->username  = $usuario->username;
        $this->email     = $usuario->email ?? '';
        $this->rol       = $usuario->rol;
        $this->password  = '';

        $this->modoEdicion  = true;
        $this->modalAbierto = true;
    }

    public function actualizar(): void
    {
        $this->validate([
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'username' => "required|string|max:50|unique:users,username,{$this->usuarioId}",
            'email'    => "nullable|email|unique:users,email,{$this->usuarioId}",
            'rol'      => 'required|in:tecnico,profesor',
            'password' => ['nullable', Password::min(8)],
        ]);

        $usuario = User::findOrFail($this->usuarioId);

        $datos = [
            'nombre'   => $this->nombre,
            'apellido' => $this->apellido,
            'username' => $this->username,
            'email'    => $this->email ?: null,
            'rol'      => $this->rol,
        ];

        if ($this->password) {
            $datos['password'] = Hash::make($this->password);
        }

        $usuario->update($datos);

        $this->modalAbierto = false;
        $this->resetForm();
        session()->flash('mensaje', 'Usuario actualizado correctamente.');
    }

    // =========================================================================
    // Acciones rápidas
    // =========================================================================

    public function toggleActivo(int $id): void
    {
        $usuario = User::findOrFail($id);

        // No permitir desactivar al propio usuario
        if ($usuario->id === auth()->id()) {
            $this->addError('general', 'No podés desactivar tu propio usuario.');
            return;
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';
        session()->flash('mensaje', "Usuario {$estado} correctamente.");
    }

    public function resetearPassword(int $id): void
    {
        $usuario = User::findOrFail($id);

        // Generar contraseña temporal
        $temporal = 'Hades' . rand(1000, 9999);
        $usuario->update(['password' => Hash::make($temporal)]);

        session()->flash('mensaje', "Contraseña reseteada. Nueva contraseña temporal: {$temporal}");
    }

    // =========================================================================

    private function resetForm(): void
    {
        $this->usuarioId = null;
        $this->nombre    = '';
        $this->apellido  = '';
        $this->username  = '';
        $this->email     = '';
        $this->rol       = 'profesor';
        $this->password  = '';
        $this->resetValidation();
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.gestion-usuarios')->title('Gestión de Usuarios');
    }
}
