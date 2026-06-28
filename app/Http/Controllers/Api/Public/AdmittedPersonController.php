<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\AdmittedPerson;
use Illuminate\Http\Request;

class AdmittedPersonController extends Controller
{
    public function index(Request $request)
    {
        $query = AdmittedPerson::where('visibility_status', 'active')
            ->with('hospital:id,name,address')
            ->select([
                'id', 'full_name', 'cedula', 'alias', 'approx_age', 'sex',
                'hospital_id', 'hospital_name_snapshot', 'status_general',
                'admitted_at', 'public_notes', 'created_at',
            ]);

        if ($request->filled('search')) {
            $search = '%' . mb_strtolower(strip_tags($request->input('search'))) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(alias) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(cedula) LIKE ?', [$search]);
            });
        }

        $localData = $query->orderBy('created_at', 'desc')->get()->toArray();

        // Fallback a API Externa
        if ($request->filled('search')) {
            try {
                $searchStr = urlencode($request->input('search'));
                $extResponse = \Illuminate\Support\Facades\Http::timeout(3)
                    ->get("https://localizadosvenezuela.com/api/v1/localizados?q={$searchStr}&limit=10");
                
                if ($extResponse->successful()) {
                    $extData = $extResponse->json();
                    $extList = isset($extData['data']) ? $extData['data'] : (is_array($extData) ? $extData : []);
                    
                    foreach ($extList as $extPerson) {
                        $extName = $extPerson['nombreCompleto'] ?? 'Desconocido';
                        $extLocation = $extPerson['lugarNombre'] ?? 'Red Nacional Externa';

                        $localData[] = [
                            'id' => 'ext_' . ($extPerson['id'] ?? uniqid()),
                            'full_name' => $extName . ' (Red Nacional)',
                            'cedula' => $extPerson['cedula'] ?? null,
                            'alias' => null,
                            'approx_age' => null,
                            'sex' => $extPerson['genero'] ?? null,
                            'hospital_name_snapshot' => $extLocation,
                            'status_general' => 'ingresada',
                            'is_external' => true,
                            'public_notes' => 'Registro obtenido de la plataforma externa localizadosvenezuela.com',
                            'created_at' => now()->toIso8601String()
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Si la API falla, simplemente ignoramos y devolvemos solo lo local
            }
        }

        return response()->json([
            'data' => $localData
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'              => 'required|string|max:255',
            'cedula'                 => 'nullable|string|max:50|unique:admitted_people,cedula',
            'alias'                  => 'nullable|string|max:255',
            'approx_age'             => 'nullable|string|max:50',
            'sex'                    => 'nullable|string|max:50',
            'hospital_id'            => 'required|exists:map_points,id',
            'hospital_name_snapshot' => 'nullable|string|max:255',
            'status_general'         => 'required|in:ingresada,en_observacion,trasladada,dada_de_alta,sin_identificar',
            'admitted_at'            => 'nullable|date|after_or_equal:2026-06-24 18:00:00',
            'public_notes'           => 'nullable|string',
            'source'                 => 'nullable|string|max:255',
            'reporter_name'          => 'nullable|string|max:255',
            'reporter_contact'       => 'nullable|string|max:255',
        ], [
            'cedula.unique' => 'Este ciudadano ya ha sido registrado, búsquelo en el módulo de buscar personas.',
            'admitted_at.after_or_equal' => 'La fecha de ingreso debe ser posterior al sismo (24 de Junio de 2026, 6:00 PM).',
            'hospital_id.required' => 'Debe seleccionar un hospital de la lista.'
        ]);

        $person = new AdmittedPerson($validated);
        $person->visibility_status = 'active'; // Inmediatamente público
        $person->save();

        return response()->json([
            'message' => 'Persona registrada correctamente.',
            'data'    => $person
        ], 201);
    }
}
