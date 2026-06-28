<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\RoadBlock;
use Illuminate\Support\Facades\Cache;

class RoadBlockController extends Controller
{
    /**
     * GET /api/public/road-blocks
     * Solo bloqueos activos, cacheado 5 minutos.
     */
    public function index()
    {
        $blocks = Cache::remember('public_road_blocks', 300, function () {
            return RoadBlock::active()
                ->select(['id', 'title', 'description', 'latitude', 'longitude', 'severity', 'source', 'updated_at'])
                ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
                ->get()->toArray();
        });

        return response()->json(['data' => $blocks]);
    }
}
