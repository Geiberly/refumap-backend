<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    private array $publicFields = [
        'id', 'name', 'email', 'status', 'organization', 'position',
        'city', 'state', 'motivation', 'coverage_area', 'created_at',
    ];

    public function index()
    {
        return response()->json([
            'data' => User::where('role', User::ROLE_OPERATOR)
                ->select($this->publicFields)
                ->orderByDesc('created_at')
                ->get()
        ]);
    }

    public function pending()
    {
        return response()->json([
            'data' => User::where('role', User::ROLE_OPERATOR)
                ->where('status', 'pending_approval')
                ->select($this->publicFields)
                ->orderByDesc('created_at')
                ->get()
        ]);
    }

    public function approve($id)
    {
        $user = User::where('role', User::ROLE_OPERATOR)->findOrFail($id);
        $user->forceFill(['status' => 'approved', 'rejection_reason' => null])->save();
        return response()->json(['message' => 'Operador aprobado.']);
    }

    public function reject(Request $request, $id)
    {
        $user = User::where('role', User::ROLE_OPERATOR)->findOrFail($id);
        $user->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason', 'Sin motivo especificado')
        ])->save();
        return response()->json(['message' => 'Operador rechazado.']);
    }

    public function disable($id)
    {
        $user = User::where('role', User::ROLE_OPERATOR)->findOrFail($id);
        $user->forceFill(['status' => 'disabled'])->save();
        return response()->json(['message' => 'Operador desactivado.']);
    }

    public function enable($id)
    {
        $user = User::where('role', User::ROLE_OPERATOR)->findOrFail($id);
        $user->forceFill(['status' => 'approved'])->save();
        return response()->json(['message' => 'Operador reactivado.']);
    }
}
