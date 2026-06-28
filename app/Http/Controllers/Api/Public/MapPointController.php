<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MapPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Support\Geo\VenezuelaBounds;

class MapPointController extends Controller
{
    /**
     * Listado público de puntos del mapa con filtros.
     * GET /api/public/map-points
     * Cacheado 3 minutos para reducir carga en situaciones de alta demanda.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'category', 'category_id', 'status', 'type', 'accepts_pets', 'accepts_children',
            'accepts_elderly', 'has_water', 'has_food', 'has_medicine',
            'has_power_charging', 'sw_lat', 'sw_lng', 'ne_lat', 'ne_lng',
            'with_capacity', 'search', 'include_unverified',
        ]);

        // Clave de caché única por combinación de filtros y versión.
        // La versión se incrementa cuando se crea/edita un punto para que el mapa refresque rápido.
        $cacheVersion = (int) Cache::get('public_map_points_version', 1);
        $cacheKey = 'public_map_points_' . $cacheVersion . '_' . md5(json_encode($filters));

        $data = Cache::remember($cacheKey, 180, function () use ($request, $filters) {
            $query = MapPoint::with(['category:id,name,slug,icon,color'])
                ->select([
                    'id', 'category_id', 'name', 'address', 'latitude', 'longitude',
                    'type', 'status', 'source', 'capacity_total', 'capacity_available',
                    'accepts_children', 'accepts_elderly', 'accepts_pets',
                    'has_water', 'has_food', 'has_medicine', 'has_power_charging',
                    'urgency_level', 'last_verified_at', 'updated_at', 'metadata'
                ])
                ->whereBetween('latitude', [VenezuelaBounds::MIN_LAT, VenezuelaBounds::MAX_LAT])
                ->whereBetween('longitude', [VenezuelaBounds::MIN_LNG, VenezuelaBounds::MAX_LNG]);

            // Excluir puntos cerrados del mapa público por defecto.
            if (!$request->has('status')) {
                $query->where('status', '!=', 'closed');
            }

            if (!empty($filters['category'])) {
                $categoryId = Category::query()
                    ->where('slug', $filters['category'])
                    ->value('id');

                if (!$categoryId) {
                    return [];
                }

                $query->where('category_id', $categoryId);
            }

            // Filtros
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (!empty($filters['search'])) {
                $search = '%' . mb_strtolower(strip_tags($filters['search'])) . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(address) LIKE ?', [$search]);
                });
            }

            // Filtros booleanos
            foreach (['accepts_pets', 'accepts_children', 'accepts_elderly', 'has_water', 'has_food', 'has_medicine', 'has_power_charging'] as $boolFilter) {
                if ($request->filled($boolFilter) && $request->boolean($boolFilter)) {
                    $query->where($boolFilter, true);
                }
            }

            // Filtro de capacidad disponible
            if ($request->boolean('with_capacity')) {
                $query->where(function ($q) {
                    $q->whereNull('capacity_available')
                      ->orWhere('capacity_available', '>', 0);
                })->where('status', 'active');
            }

            // Filtro de bounds del mapa (para cargar solo puntos visibles)
            if (!empty($filters['sw_lat']) && !empty($filters['sw_lng']) && !empty($filters['ne_lat']) && !empty($filters['ne_lng'])) {
                $query->withinBounds(
                    $filters['sw_lat'], $filters['sw_lng'],
                    $filters['ne_lat'], $filters['ne_lng']
                );
            }

            $query->orderByDesc('urgency_level')->orderByDesc('updated_at');

            return $query->get()->toArray();
        });

        return response()->json([
            'data'  => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Detalle de un punto específico.
     * GET /api/public/map-points/{id}
     */
    public function show(int $id)
    {
        $point = Cache::remember("public_map_point_{$id}", 120, function () use ($id) {
            return MapPoint::with(['category:id,name,slug,icon,color'])
                ->whereBetween('latitude', [VenezuelaBounds::MIN_LAT, VenezuelaBounds::MAX_LAT])
                ->whereBetween('longitude', [VenezuelaBounds::MIN_LNG, VenezuelaBounds::MAX_LNG])
                ->findOrFail($id)
                ->toArray();
        });

        return response()->json(['data' => $point]);
    }
}
