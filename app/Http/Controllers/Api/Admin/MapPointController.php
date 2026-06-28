<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapPointRequest;
use App\Http\Requests\UpdateMapPointRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\MapPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapPointController extends Controller
{
    /**
     * GET /api/admin/map-points
     */
    public function index(Request $request)
    {
        $query = MapPoint::with(['category:id,name,slug,icon,color', 'creator:id,name'])
            ->select([
                'id', 'category_id', 'name', 'address', 'latitude', 'longitude',
                'type', 'status', 'source', 'capacity_total', 'capacity_available',
                'urgency_level', 'last_verified_at', 'created_by', 'updated_at',
            ]);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('urgency_level')) {
            $query->where('urgency_level', $request->urgency_level);
        }

        if ($request->filled('search')) {
            $search = '%' . mb_strtolower(strip_tags($request->search)) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(address) LIKE ?', [$search]);
            });
        }

        $points = $query->orderByDesc('urgency_level')
                        ->orderByDesc('updated_at')
                        ->paginate(25);

        return response()->json($points);
    }

    /**
     * POST /api/admin/map-points
     */
    public function store(StoreMapPointRequest $request)
    {
        $data = $request->validated();
        
        $point = new MapPoint($data);
        $point->type = $this->resolveType($data['category_id']);
        $point->created_by = $request->user()->id;
        $point->updated_by = $request->user()->id;
        $point->last_verified_at = now();
        $point->status = $data['status'] ?? 'unverified';
        $point->source = $data['source'] ?? 'official';

        $point->save();

        AuditLog::record('created', 'MapPoint', $point->id, null, $point->toArray());

        $this->clearCache();

        return response()->json([
            'message' => 'Punto creado exitosamente.',
            'data'    => $point->load('category:id,name,slug,icon,color'),
        ], 201);
    }

    /**
     * GET /api/admin/map-points/{id}
     */
    public function show(int $id)
    {
        $point = MapPoint::with(['category', 'creator:id,name', 'updater:id,name'])->findOrFail($id);
        return response()->json(['data' => $point]);
    }

    /**
     * PUT /api/admin/map-points/{id}
     */
    public function update(UpdateMapPointRequest $request, int $id)
    {
        $point = MapPoint::findOrFail($id);
        $oldValues = $point->toArray();

        $data = $request->validated();
        $point->fill($data);
        
        $point->type = $this->resolveType($data['category_id'] ?? $point->category_id);
        $point->updated_by = $request->user()->id;

        if (isset($data['status'])) {
            $point->status = $data['status'];
        }
        
        if (isset($data['source'])) {
            $point->source = $data['source'];
        }

        // Si se cambia el estado a active/official, actualizar last_verified_at
        if (in_array($point->status, ['active']) &&
            in_array($point->source, ['official', 'operator'])) {
            $point->last_verified_at = now();
        }

        $point->save();

        AuditLog::record('updated', 'MapPoint', $point->id, $oldValues, $point->fresh()->toArray());

        $this->clearCache($id);

        return response()->json([
            'message' => 'Punto actualizado exitosamente.',
            'data'    => $point->load('category:id,name,slug,icon,color'),
        ]);
    }

    /**
     * DELETE /api/admin/map-points/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $point = MapPoint::findOrFail($id);

        AuditLog::record('deleted', 'MapPoint', $point->id, $point->toArray(), null);

        $point->delete();

        $this->clearCache($id);

        return response()->json(['message' => 'Punto eliminado exitosamente.']);
    }

    private function clearCache(?int $id = null): void
    {
        Cache::forget('public_categories');
        Cache::forget('public_stats');
        Cache::forever('public_map_points_version', (int) Cache::get('public_map_points_version', 1) + 1);

        if ($id) {
            Cache::forget("public_map_point_{$id}");
        }
    }

    private function resolveType(int $categoryId): string
    {
        return Category::query()->whereKey($categoryId)->value('slug') ?? 'other';
    }
}
