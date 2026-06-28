<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\MapPoint;
use App\Models\RoadBlock;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    /**
     * GET /api/public/stats
     * Métricas rápidas para mostrar en el mapa.
     * Cacheado 5 minutos.
     */
    public function index()
    {
        $stats = Cache::remember('public_stats', 300, function () {
            return [
                'refugios_activos'       => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'refugio'))
                                                    ->where('status', 'active')->count(),
                'refugios_saturados'     => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'refugio'))
                                                    ->where('status', 'full')->count(),
                'hospitales_operativos'  => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'hospital'))
                                                    ->where('status', 'active')->count(),
                'total_puntos_activos'   => MapPoint::whereIn('status', ['active', 'full'])->count(),
                'reportes_pendientes'    => CitizenReport::pending()->count(),
                'zonas_criticas'         => MapPoint::where('status', 'danger')
                                                    ->orWhere('urgency_level', 4)->count(),
                'bloqueos_activos'       => RoadBlock::active()->count(),
                'ultima_actualizacion'   => MapPoint::max('updated_at'),
            ];
        });

        return response()->json(['data' => $stats]);
    }
}
