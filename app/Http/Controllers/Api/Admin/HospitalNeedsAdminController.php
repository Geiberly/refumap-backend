<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapPoint;
use App\Models\HospitalNeed;
use App\Models\HospitalNeedReport;
use Illuminate\Http\Request;

class HospitalNeedsAdminController extends Controller
{
    public function updateNeed(Request $request, $id)
    {
        $validated = $request->validate([
            'hospital_id' => 'required|exists:map_points,id',
            'need_type'   => 'required|string',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,critical',
            'quantity'    => 'nullable|string',
            'status'      => 'required|in:active,fulfilled'
        ]);

        $need = HospitalNeed::find($id);
        
        if ($need) {
            $need->fill($validated);
        } else {
            $need = new HospitalNeed($validated);
        }
        
        $need->forceFill([
            'status' => $validated['status'],
            'updated_by' => $request->user()->id
        ])->save();

        return response()->json([
            'message' => 'Necesidad actualizada.',
            'data'    => $need
        ]);
    }

    public function reports()
    {
        return response()->json([
            'data' => HospitalNeedReport::orderBy('created_at', 'desc')->get()
        ]);
    }

    public function updateReportStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:reviewed,rejected'
        ]);

        $report = HospitalNeedReport::findOrFail($id);
        
        $report->forceFill([
            'status'      => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now()
        ])->save();

        return response()->json([
            'message' => 'Reporte actualizado.',
            'data'    => $report
        ]);
    }
}
