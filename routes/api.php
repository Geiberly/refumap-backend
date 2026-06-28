<?php

use App\Http\Controllers\Api\Admin\CitizenReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\MapPointController as AdminMapPointController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\CitizenReportController as PublicReportController;
use App\Http\Controllers\Api\Public\MapPointController as PublicMapPointController;
use App\Http\Controllers\Api\Public\RoadBlockController;
use App\Http\Controllers\Api\Public\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — RefuMap
|--------------------------------------------------------------------------
*/

// ─── RUTAS PÚBLICAS (sin autenticación) ────────────────────────────────────
Route::prefix('public')->name('public.')->group(function () {
    Route::get('map-points',       [PublicMapPointController::class, 'index'])
         ->name('map-points.index')->middleware('throttle:60,1');
    Route::get('map-points/{id}',  [PublicMapPointController::class, 'show'])
         ->name('map-points.show')->middleware('throttle:60,1');
    Route::get('categories',       [CategoryController::class, 'index'])
         ->name('categories.index')->middleware('throttle:60,1');
    Route::get('road-blocks',      [RoadBlockController::class, 'index'])
         ->name('road-blocks.index')->middleware('throttle:60,1');
    Route::get('stats',            [StatsController::class, 'index'])
         ->name('stats.index')->middleware('throttle:60,1');

    // Personas ingresadas (público) - throttle 20/min
    Route::get('admitted-people',  [\App\Http\Controllers\Api\Public\AdmittedPersonController::class, 'index'])
         ->name('admitted-people.index')
         ->middleware('throttle:20,1');
    Route::post('admitted-people', [\App\Http\Controllers\Api\Public\AdmittedPersonController::class, 'store'])
         ->name('admitted-people.store')
         ->middleware('throttle:10,1');

    // Hospitales y necesidades (público)
    Route::get('hospitals', [\App\Http\Controllers\Api\Public\HospitalNeedsController::class, 'index'])->name('hospitals.index');
    Route::get('hospitals/{id}', [\App\Http\Controllers\Api\Public\HospitalNeedsController::class, 'show'])->name('hospitals.show');
    Route::post('hospital-needs-report', [\App\Http\Controllers\Api\Public\HospitalNeedsController::class, 'reportNeed'])
         ->name('hospitals.needs.report')
         ->middleware('throttle:10,1');

    // Reportes de Puntos en el Mapa (público) - Máximo inicial de 10/min para prevenir spam masivo
    Route::post('refuges', [\App\Http\Controllers\Api\Public\MapPointReportController::class, 'storeRefuge'])
         ->name('reports.refuge')->middleware('throttle:10,1');
         
    Route::post('hospitals-report', [\App\Http\Controllers\Api\Public\MapPointReportController::class, 'storeHospital'])
         ->name('reports.hospital')->middleware('throttle:10,1');
         
    Route::post('road-issues', [\App\Http\Controllers\Api\Public\MapPointReportController::class, 'storeRoadIssue'])
         ->name('reports.road-issue')->middleware('throttle:10,1');
         
    Route::post('danger-zones', [\App\Http\Controllers\Api\Public\MapPointReportController::class, 'storeDangerZone'])
         ->name('reports.danger-zone')->middleware('throttle:10,1');
         
    Route::post('help-points', [\App\Http\Controllers\Api\Public\MapPointReportController::class, 'storeHelpPoint'])
         ->name('reports.help-point')->middleware('throttle:10,1');
    Route::post('reports', [PublicReportController::class, 'store'])
         ->name('reports.store')
         ->middleware('throttle:10,1');
         
    // Asistente IA (OpenRouter)
    Route::post('chat', [\App\Http\Controllers\Api\Public\ChatController::class, 'chat'])
         ->name('chat')
         ->middleware('throttle:15,1');
});

// ─── AUTENTICACIÓN ─────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login')
         ->middleware('throttle:5,1'); // Anti-brute force
         
    Route::post('operator-register', [\App\Http\Controllers\Api\Auth\OperatorRegisterController::class, 'store'])
         ->name('operator.register')
         ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me',      [AuthController::class, 'me'])->name('me');
    });
});

// ─── PANEL OPERADOR / ADMIN ─────────────────────────────────────────────────
Route::prefix('refu-control')->name('admin.')->middleware(['auth:sanctum', 'role:operator'])->group(function () {

    // Dashboard (admin y operator)
    Route::get('dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // CRUD de puntos del mapa
    Route::apiResource('map-points', AdminMapPointController::class)->names('map-points');

    // Gestión de reportes ciudadanos
    Route::get('reports',                       [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{id}',                  [AdminReportController::class, 'show'])->name('reports.show');
    Route::put('reports/{id}/verify',           [AdminReportController::class, 'verify'])->name('reports.verify');
    Route::put('reports/{id}/reject',           [AdminReportController::class, 'reject'])->name('reports.reject');
    Route::post('reports/{id}/convert-to-map-point', [AdminReportController::class, 'convertToMapPoint'])
         ->name('reports.convert');

    // Gestión de usuarios (solo admin)
    Route::apiResource('users', UserController::class)
         ->middleware('role:admin')
         ->names('users');

    // Gestión de operadores (solo admin)
    Route::get('operators', [App\Http\Controllers\Api\Admin\OperatorController::class, 'index'])->name('operators.index')->middleware('role:admin');
    Route::get('operators/pending', [App\Http\Controllers\Api\Admin\OperatorController::class, 'pending'])->name('operators.pending')->middleware('role:admin');
    Route::patch('operators/{id}/approve', [App\Http\Controllers\Api\Admin\OperatorController::class, 'approve'])->name('operators.approve')->middleware('role:admin');
    Route::patch('operators/{id}/reject', [App\Http\Controllers\Api\Admin\OperatorController::class, 'reject'])->name('operators.reject')->middleware('role:admin');
    Route::patch('operators/{id}/disable', [App\Http\Controllers\Api\Admin\OperatorController::class, 'disable'])->name('operators.disable')->middleware('role:admin');
    Route::patch('operators/{id}/enable', [App\Http\Controllers\Api\Admin\OperatorController::class, 'enable'])->name('operators.enable')->middleware('role:admin');

    // Gestión de personas ingresadas (operadores/admin)
    Route::get('admitted-people', [App\Http\Controllers\Api\Admin\AdmittedPersonAdminController::class, 'index'])->name('admin.admitted-people.index');
    Route::put('admitted-people/{id}', [App\Http\Controllers\Api\Admin\AdmittedPersonAdminController::class, 'update'])->name('admin.admitted-people.update');
    Route::patch('admitted-people/{id}/status', [App\Http\Controllers\Api\Admin\AdmittedPersonAdminController::class, 'updateStatus'])->name('admin.admitted-people.status');

    // Gestión de necesidades hospitalarias (operadores/admin)
    Route::post('hospitals/needs/{id}', [App\Http\Controllers\Api\Admin\HospitalNeedsAdminController::class, 'updateNeed'])->name('admin.hospitals.needs.update');
    Route::get('hospital-needs-reports', [App\Http\Controllers\Api\Admin\HospitalNeedsAdminController::class, 'reports'])->name('admin.hospital-needs-reports.index');
    Route::patch('hospital-needs-reports/{id}/status', [App\Http\Controllers\Api\Admin\HospitalNeedsAdminController::class, 'updateReportStatus'])->name('admin.hospital-needs-reports.status');
});
