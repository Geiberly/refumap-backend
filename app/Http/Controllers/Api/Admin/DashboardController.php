<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\MapPoint;
use App\Models\RoadBlock;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard
     * Métricas completas para el panel administrador.
     * Cacheado 2 minutos.
     */
    public function index()
    {
        $stats = Cache::remember('admin_dashboard_stats', 120, function () {
            return [
                // Refugios
                'refugios_activos'       => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'refugio'))
                                                    ->where('status', 'active')->count(),
                'refugios_saturados'     => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'refugio'))
                                                    ->where('status', 'full')->count(),
                'refugios_cerrados'      => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'refugio'))
                                                    ->where('status', 'closed')->count(),

                // Hospitales
                'hospitales_operativos'  => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'hospital'))
                                                    ->where('status', 'active')->count(),
                'hospitales_cerrados'    => MapPoint::whereHas('category', fn($q) => $q->where('slug', 'hospital'))
                                                    ->whereIn('status', ['closed', 'danger'])->count(),

                // Puntos totales
                'total_puntos'           => MapPoint::count(),
                'puntos_activos'         => MapPoint::where('status', 'active')->count(),
                'puntos_peligro'         => MapPoint::where('status', 'danger')->count(),
                'puntos_no_verificados'  => MapPoint::where('status', 'unverified')->count(),

                // Reportes
                'reportes_pendientes'    => CitizenReport::pending()->count(),
                'reportes_verificados'   => CitizenReport::where('status', 'verified')->count(),
                'reportes_convertidos'   => CitizenReport::where('status', 'converted')->count(),
                'reportes_rechazados'    => CitizenReport::where('status', 'rejected')->count(),

                // Bloqueos
                'bloqueos_activos'       => RoadBlock::active()->count(),
                'bloqueos_criticos'      => RoadBlock::active()->where('severity', 'critical')->count(),

                // Usuarios
                'operadores_activos'     => User::where('role', 'operator')->where('status', 'approved')->count(),

                // Actividad reciente
                'ultima_actualizacion'   => MapPoint::max('updated_at'),

                // Puntos recientes
                'puntos_recientes'       => MapPoint::with('category:id,name,slug,icon,color')
                                                    ->select(['id', 'category_id', 'name', 'status', 'urgency_level', 'updated_at'])
                                                    ->orderByDesc('updated_at')
                                                    ->limit(5)
                                                    ->get()->toArray(),

                // Reportes pendientes recientes
                'reportes_recientes'     => CitizenReport::select(['id', 'report_type', 'title', 'status', 'created_at'])
                                                         ->pending()
                                                         ->orderByDesc('created_at')
                                                         ->limit(5)
                                                         ->get()->toArray(),

                // Operadores recientes
                'operadores_recientes'   => User::where('role', 'operator')
                                                ->orderByDesc('created_at')
                                                ->limit(3)
                                                ->get(['id', 'name', 'role', 'status', 'created_at'])->toArray(),
            ];
        });

        return response()->json(['data' => $stats]);
    }
}
