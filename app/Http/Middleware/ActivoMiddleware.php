<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActivoMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \Illuminate\Auth\Guard $auth */
        $auth = auth();
        if ($auth->check() && !$auth->user()->estaActivo()) {
            $auth->logout();
            return redirect()->route('login')
                ->withErrors(['username' => 'Tu cuenta está desactivada. Contactá a un técnico.']);
        }

        return $next($request);
    }
}
