<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TecnicoMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->esTecnico()) {
            abort(403, 'Acceso restringido a técnicos.');
        }

        return $next($request);
    }
}
