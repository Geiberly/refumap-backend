<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmittedPerson;
use Illuminate\Http\Request;

class AdmittedPersonAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = AdmittedPerson::with('hospital:id,name')
            ->select([
                'id', 'full_name', 'alias', 'approx_age', 'sex',
                'hospital_id', 'hospital_name_snapshot', 'status_general',
                'visibility_status', 'reporter_name', 'reporter_contact',
                'admitted_at', 'public_notes', 'created_at',
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = '%' . mb_strtolower(strip_tags($request->input('search'))) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(alias) LIKE ?', [$search]);
            });
        }

        if ($request->filled('visibility_status')) {
            $query->where('visibility_status', $request->input('visibility_status'));
        }

        return response()->json($query->paginate(25));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'visibility_status' => 'required|in:active,hidden,duplicate,removed'
        ]);

        $person = AdmittedPerson::findOrFail($id);
        $status = $request->visibility_status;
        
        $updateData = ['visibility_status' => $status];

        if ($status === 'hidden') {
            $updateData['hidden_by'] = $request->user()->id;
            $updateData['hidden_at'] = now();
        } elseif ($status === 'removed') {
            $updateData['removed_by'] = $request->user()->id;
            $updateData['removed_at'] = now();
        }

        $person->forceFill($updateData)->save();

        return response()->json([
            'message' => 'Estado de visibilidad actualizado.',
            'data'    => $person
        ]);
    }

    public function update(Request $request, $id)
    {
        $person = AdmittedPerson::findOrFail($id);
        
        $validated = $request->validate([
            'full_name'              => 'sometimes|string|max:255',
            'alias'                  => 'nullable|string|max:255',
            'approx_age'             => 'nullable|string|max:50',
            'sex'                    => 'nullable|string|max:50',
            'hospital_id'            => 'nullable|exists:map_points,id',
            'hospital_name_snapshot' => 'nullable|string|max:255',
            'status_general'         => 'sometimes|in:ingresada,en_observacion,trasladada,dada_de_alta,sin_identificar',
            'public_notes'           => 'nullable|string',
        ]);

        $person->update($validated);

        return response()->json([
            'message' => 'Registro actualizado.',
            'data'    => $person
        ]);
    }
}
