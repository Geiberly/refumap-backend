<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CitizenReport;
use App\Models\MapPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Support\Geo\VenezuelaBounds;

class CitizenReportController extends Controller
{
    /**
     * GET /api/admin/reports
     */
    public function index(Request $request)
    {
        $query = CitizenReport::with(['reviewer:id,name'])
            ->select([
                'id', 'report_type', 'title', 'status', 'latitude', 'longitude',
                'address', 'contact_phone', 'photo_path', 'reviewed_by',
                'reviewed_at', 'converted_map_point_id', 'created_at',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($reports);
    }

    /**
     * GET /api/admin/reports/{id}
     */
    public function show(int $id)
    {
        $report = CitizenReport::with(['reviewer:id,name', 'convertedMapPoint:id,name'])->findOrFail($id);
        return response()->json(['data' => $report]);
    }

    /**
     * PUT /api/admin/reports/{id}/verify
     */
    public function verify(Request $request, int $id)
    {
        $report = CitizenReport::findOrFail($id);

        if ($report->status !== CitizenReport::STATUS_PENDING) {
            return response()->json(['message' => 'El reporte ya fue procesado.'], 422);
        }

        $report->forceFill([
            'status'         => CitizenReport::STATUS_VERIFIED,
            'reviewed_by'    => $request->user()->id,
            'reviewed_at'    => now(),
            'reviewer_notes' => $request->input('notes'),
        ])->save();

        AuditLog::record('verified', 'CitizenReport', $report->id);

        return response()->json(['message' => 'Reporte verificado correctamente.', 'data' => $report]);
    }

    /**
     * PUT /api/admin/reports/{id}/reject
     */
    public function reject(Request $request, int $id)
    {
        $report = CitizenReport::findOrFail($id);

        if ($report->status !== CitizenReport::STATUS_PENDING) {
            return response()->json(['message' => 'El reporte ya fue procesado.'], 422);
        }

        $report->forceFill([
            'status'         => CitizenReport::STATUS_REJECTED,
            'reviewed_by'    => $request->user()->id,
            'reviewed_at'    => now(),
            'reviewer_notes' => $request->input('notes'),
        ])->save();

        AuditLog::record('rejected', 'CitizenReport', $report->id);

        return response()->json(['message' => 'Reporte rechazado.', 'data' => $report]);
    }

    /**
     * POST /api/admin/reports/{id}/convert-to-map-point
     * Convierte un reporte ciudadano en un punto oficial del mapa.
     */
    public function convertToMapPoint(Request $request, int $id)
    {
        $report = CitizenReport::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:200',
            'status'      => 'required|in:active,full,closed,unverified,danger',
            'source'      => 'required|in:official,operator,citizen,seed,unverified',
        ], [
            'category_id.required' => 'Debes seleccionar una categoría.',
            'name.required'        => 'El nombre del punto es obligatorio.',
        ]);

        if (!VenezuelaBounds::contains($report->latitude, $report->longitude)) {
            return response()->json(['message' => VenezuelaBounds::message()], 422);
        }

        $metadata = $report->metadata ?? [];
        $categorySlug = Category::query()->whereKey($request->category_id)->value('slug') ?? 'other';

        $mapPoint = new MapPoint([
            'category_id'    => $request->category_id,
            'name'           => strip_tags($request->name),
            'description'    => $report->description,
            'address'        => $report->address,
            'latitude'       => $report->latitude,
            'longitude'      => $report->longitude,
            'notes'          => "Convertido desde reporte ciudadano #" . $report->id,
            // Metadatos y campos dinámicos
            'capacity_total'      => $metadata['capacity_total'] ?? null,
            'capacity_available'  => $metadata['capacity_available'] ?? null,
            'accepts_children'    => $metadata['accepts_children'] ?? false,
            'accepts_elderly'     => $metadata['accepts_elderly'] ?? false,
            'accepts_pets'        => $metadata['accepts_pets'] ?? false,
            'has_water'           => $metadata['has_water'] ?? false,
            'has_food'            => $metadata['has_food'] ?? false,
            'has_medicine'        => $metadata['has_medicine'] ?? false,
            'has_power_charging'  => $metadata['has_power_charging'] ?? false,
            'contact_phone'       => $metadata['contact_phone'] ?? $report->contact_phone,
            'emergency_available' => $metadata['emergency_available'] ?? false,
            'needs_supplies'      => $metadata['needs_supplies'] ?? false,
            'metadata'            => $metadata,
        ]);
        
        $mapPoint->forceFill([
            'type'           => $categorySlug,
            'status'         => $request->status,
            'source'         => $request->source,
            'created_by'     => $request->user()->id,
            'updated_by'     => $request->user()->id,
            'last_verified_at' => now(),
        ])->save();

        $report->forceFill([
            'status'                  => CitizenReport::STATUS_CONVERTED,
            'reviewed_by'             => $request->user()->id,
            'reviewed_at'             => now(),
            'converted_map_point_id'  => $mapPoint->id,
        ])->save();

        AuditLog::record('converted', 'CitizenReport', $report->id, null, ['map_point_id' => $mapPoint->id]);
        AuditLog::record('created', 'MapPoint', $mapPoint->id, null, $mapPoint->toArray());

        Cache::forget('public_stats');
        Cache::forever('public_map_points_version', (int) Cache::get('public_map_points_version', 1) + 1);

        return response()->json([
            'message'   => 'Reporte convertido a punto del mapa exitosamente.',
            'map_point' => $mapPoint->load('category:id,name,slug,icon,color'),
        ], 201);
    }
}
