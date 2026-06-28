<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\MapPoint;
use App\Models\HospitalNeed;
use App\Models\HospitalNeedReport;
use Illuminate\Http\Request;

class HospitalNeedsController extends Controller
{
    public function index()
    {
        // Obtener todos los hospitales con sus necesidades
        $hospitals = MapPoint::whereHas('category', function($q) {
            $q->where('slug', 'hospital');
        })->with('hospitalNeeds')->get();

        return response()->json([
            'data' => $hospitals
        ]);
    }

    public function show($id)
    {
        $hospital = MapPoint::whereHas('category', function($q) {
            $q->where('slug', 'hospital');
        })->with('hospitalNeeds')->findOrFail($id);

        return response()->json([
            'data' => $hospital
        ]);
    }

    public function reportNeed(Request $request)
    {
        $validated = $request->validate([
            'hospital_id'      => 'required|exists:map_points,id',
            'needs'            => 'required|string',
            'description'      => 'nullable|string',
            'reporter_name'    => 'nullable|string|max:255',
            'reporter_contact' => 'nullable|string|max:255',
        ]);

        $hospital = MapPoint::findOrFail($validated['hospital_id']);

        $report = new HospitalNeedReport($validated);
        $report->hospital_name = $hospital->name;
        $report->status = 'pending';
        $report->save();

        return response()->json([
            'message' => 'Reporte enviado. Será revisado por un operador.',
            'data'    => $report
        ], 201);
    }
}
