<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.auth')]
#[Title('Iniciar sesión')]
class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt([
            'username' => $this->username,
            'password' => $this->password,
        ], $this->remember)) {

            $this->addError('username', 'Credenciales incorrectas.');
            return;
        }

        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}