<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/admin/users
     * Solo accesible por admin.
     */
    public function index(Request $request)
    {
        $users = User::select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->orderBy('name')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * POST /api/admin/users
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:operator,admin',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.email'        => 'El correo electrónico no es válido.',
            'email.unique'       => 'Ya existe un usuario con ese correo.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required'      => 'El rol es obligatorio.',
            'role.in'            => 'El rol debe ser operador o administrador.',
        ]);

        $user = new User([
            'name'      => strip_tags($data['name']),
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
        ]);
        
        $user->forceFill([
            'role'      => $data['role'],
            'status'    => 'approved',
        ])->save();

        AuditLog::record('created', 'User', $user->id, null, ['name' => $user->name, 'role' => $user->role]);

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            'data'    => $user->only(['id', 'name', 'email', 'role', 'status']),
        ], 201);
    }

    /**
     * PUT /api/admin/users/{id}
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'email'     => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'password'  => 'sometimes|string|min:8',
            'role'      => 'sometimes|in:operator,admin',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $fillData = $data;
        $forceFillData = [];
        
        if (isset($fillData['role'])) {
            $forceFillData['role'] = $fillData['role'];
            unset($fillData['role']);
        }

        if (isset($fillData['is_active'])) {
            $forceFillData['status'] = $fillData['is_active'] ? 'approved' : 'disabled';
            unset($fillData['is_active']);
        }

        $oldValues = $user->only(['name', 'email', 'role', 'status']);
        
        $user->fill($fillData);
        if (!empty($forceFillData)) {
            $user->forceFill($forceFillData);
        }
        $user->save();

        AuditLog::record('updated', 'User', $user->id, $oldValues, $user->fresh()->only(['name', 'email', 'role', 'status']));

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data'    => $user->only(['id', 'name', 'email', 'role', 'status']),
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}
     * Solo desactiva al usuario (no elimina datos).
     */
    public function destroy(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes desactivar tu propia cuenta.'], 422);
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        AuditLog::record('deactivated', 'User', $user->id);

        return response()->json(['message' => 'Usuario desactivado correctamente.']);
    }
}
