<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verificar que el usuario autenticado tenga el rol requerido.
     * Uso en rutas: middleware('role:admin') o middleware('role:operator')
     * El rol 'operator' también permite acceso a administradores.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || $user->status !== 'approved') {
            return response()->json([
                'message' => 'No autorizado o cuenta pendiente de aprobación.',
            ], 403);
        }

        // Admin puede todo
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar si tiene alguno de los roles requeridos
        foreach ($roles as $role) {
            if ($role === 'operator' && $user->isOperator()) {
                return $next($request);
            }
            if ($role === $user->role) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'No tienes permisos para realizar esta acción.',
        ], 403);
    }
}
