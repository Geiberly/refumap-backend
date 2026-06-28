<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OperatorRegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'organization'  => 'nullable|string|max:255',
            'position'      => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'state'         => 'nullable|string|max:255',
            'motivation'    => 'nullable|string',
            'coverage_area' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'Este correo ya está registrado en el sistema.',
        ]);

        $user = new User([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'organization'  => $validated['organization'] ?? null,
            'position'      => $validated['position'] ?? null,
            'city'          => $validated['city'] ?? null,
            'state'         => $validated['state'] ?? null,
            'motivation'    => $validated['motivation'] ?? null,
            'coverage_area' => $validated['coverage_area'] ?? null,
        ]);
        
        $user->forceFill([
            'role'          => User::ROLE_OPERATOR,
            'status'        => 'pending_approval',
        ])->save();

        return response()->json([
            'message' => 'Registro completado. Tu solicitud está pendiente de aprobación por un administrador.',
            'user'    => $user,
        ], 201);
    }
}
