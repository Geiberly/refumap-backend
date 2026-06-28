<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitizenReportRequest;
use App\Models\CitizenReport;
use Illuminate\Support\Facades\Storage;

class CitizenReportController extends Controller
{
    /**
     * POST /api/public/reports
     * Rate limit: 10 por minuto por IP (configurado en rutas).
     */
    public function store(StoreCitizenReportRequest $request)
    {
        $data = $request->validated();

        // Manejar foto si existe
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports/photos', 'public');
        }

        $report = new CitizenReport([
            'report_type'   => $data['report_type'],
            'title'         => strip_tags($data['title']),
            'description'   => isset($data['description']) ? strip_tags($data['description']) : null,
            'latitude'      => $data['latitude'] ?? null,
            'longitude'     => $data['longitude'] ?? null,
            'address'       => isset($data['address']) ? strip_tags($data['address']) : null,
            'photo_path'    => $photoPath,
            'contact_phone' => $data['contact_phone'] ?? null,
            'metadata'      => $data['metadata'] ?? null,
        ]);
        
        $report->reporter_ip = null; // A06: Privacidad - No guardamos IP
        $report->status = CitizenReport::STATUS_PENDING;
        $report->save();

        return response()->json([
            'message' => 'Tu reporte fue enviado exitosamente. Será revisado por nuestro equipo.',
            'data'    => [
                'id'     => $report->id,
                'status' => $report->status,
            ],
        ], 201);
    }
}
