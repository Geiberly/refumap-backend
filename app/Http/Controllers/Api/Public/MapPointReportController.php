<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MapPoint;
use App\Support\Geo\VenezuelaBounds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapPointReportController extends Controller
{
    private function getCategoryId(string $slug): ?int
    {
        return Category::where('slug', $slug)->value('id');
    }

    private function bumpPublicMapCache(): void
    {
        // Los listados públicos usan cache por filtros; versionar la clave evita que un reporte nuevo tarde minutos en verse.
        Cache::forever('public_map_points_version', (int) Cache::get('public_map_points_version', 1) + 1);
        Cache::forget('public_stats');
    }

    private function createReport(Request $request, string $categorySlug, array $extraFields = [], array $metadataFields = [])
    {
        $baseRules = [
            'name'          => 'required|string|max:255',
            'latitude'      => VenezuelaBounds::latitudeRule(),
            'longitude'     => VenezuelaBounds::longitudeRule(),
            'address'       => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'contact_phone' => 'nullable|string|max:30',
            'notes'         => 'nullable|string',
        ];

        $directFieldsRules = [
            'capacity_total'     => 'nullable|integer|min:0',
            'capacity_available' => 'nullable|integer|min:0',
            'accepts_children'   => 'nullable|boolean',
            'accepts_elderly'    => 'nullable|boolean',
            'accepts_pets'       => 'nullable|boolean',
            'has_water'          => 'nullable|boolean',
            'has_food'           => 'nullable|boolean',
            'has_medicine'       => 'nullable|boolean',
            'has_power_charging' => 'nullable|boolean',
            'urgency_level'      => 'nullable|integer|min:1|max:4',
        ];

        // Validamos explícitamente todos los campos extra para evitar Mass Assignment / Inyecciones (A08)
        $validated = $request->validate(array_merge($baseRules, $extraFields, $directFieldsRules), [
            'latitude.between' => VenezuelaBounds::message(),
            'longitude.between' => VenezuelaBounds::message(),
        ]);

        // Defensa extra para el flujo público: nunca persistir puntos fuera de Venezuela.
        if (!VenezuelaBounds::contains($validated['latitude'], $validated['longitude'])) {
            return response()->json([
                'message' => VenezuelaBounds::message(),
                'errors' => [
                    'latitude' => [VenezuelaBounds::message()],
                    'longitude' => [VenezuelaBounds::message()],
                ],
            ], 422);
        }

        $categoryId = $this->getCategoryId($categorySlug);

        if (!$categoryId) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $metadata = [];
        foreach ($metadataFields as $field) {
            if (array_key_exists($field, $validated)) {
                $metadata[$field] = $validated[$field];
            }
        }

        $data = [
            'category_id'   => $categoryId,
            'name'          => strip_tags($validated['name']),
            'type'          => $categorySlug,
            'latitude'      => (float) $validated['latitude'],
            'longitude'     => (float) $validated['longitude'],
            'address'       => isset($validated['address']) ? strip_tags($validated['address']) : null,
            'description'   => isset($validated['description']) ? strip_tags($validated['description']) : null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'notes'         => isset($validated['notes']) ? strip_tags($validated['notes']) : null,
            'status'        => 'unverified',
            'source'        => 'citizen',
            'metadata'      => !empty($metadata) ? $metadata : null,
        ];

        $directFields = array_keys($directFieldsRules);

        foreach ($directFields as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $validated[$field];
            }
        }

        $mapPoint = MapPoint::create($data);
        $this->bumpPublicMapCache();

        return response()->json([
            'message' => 'Reporte enviado correctamente. Será validado pronto.',
            'data'    => $mapPoint->load('category:id,name,slug,icon,color'),
        ], 201);
    }

    public function storeRefuge(Request $request)
    {
        return $this->createReport($request, 'refugio', [
            'capacity_total' => 'nullable|integer',
            'accepts_children' => 'boolean',
            'accepts_elderly' => 'boolean',
            'accepts_pets' => 'boolean',
            'reporter_name' => 'nullable|string|max:255',
        ], ['needs', 'reporter_name']);
    }

    public function storeHospital(Request $request)
    {
        return $this->createReport($request, 'hospital', [
            'hospital_type' => 'nullable|string',
            'operative_status' => 'nullable|string',
            'services' => 'nullable|array',
            'needs' => 'nullable|array',
            'reporter_name' => 'nullable|string|max:255',
        ], ['hospital_type', 'operative_status', 'services', 'needs', 'reporter_name']);
    }

    public function storeRoadIssue(Request $request)
    {
        return $this->createReport($request, 'via-bloqueada', [
            'issue_type' => 'nullable|string',
            'urgency_level' => 'nullable|integer|min:1|max:4',
            'reporter_name' => 'nullable|string|max:255',
        ], ['issue_type', 'reporter_name']);
    }

    public function storeDangerZone(Request $request)
    {
        return $this->createReport($request, 'zona-peligrosa', [
            'danger_type' => 'nullable|string',
            'urgency_level' => 'nullable|integer|min:1|max:4',
            'reporter_name' => 'nullable|string|max:255',
        ], ['danger_type', 'reporter_name']);
    }

    public function storeHelpPoint(Request $request)
    {
        return $this->createReport($request, 'centro-acopio', [
            'reporter_name' => 'nullable|string|max:255',
            'help_types' => 'required|array|min:1',
            'help_types.*' => 'string',
        ], ['reporter_name', 'help_types']);
    }
}
