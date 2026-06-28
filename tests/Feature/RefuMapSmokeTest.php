<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CitizenReport;
use App\Models\MapPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RefuMapSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_public_map_endpoints_include_unverified_hospitals(): void
    {
        $hospitalCategory = Category::query()->where('slug', 'hospital')->first();

        $this->assertNotNull($hospitalCategory);

        $response = $this->getJson('/api/public/categories');
        $response->assertOk();
        $this->assertTrue(collect($response->json('data'))->contains(fn ($category) => $category['slug'] === 'hospital'));

        $allPoints = $this->getJson('/api/public/map-points');
        $allPoints->assertOk()->assertJsonStructure(['data', 'total']);

        $hospitalPoints = $this->getJson('/api/public/map-points?category=hospital&include_unverified=true');
        $hospitalPoints->assertOk();

        $data = $hospitalPoints->json('data');

        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->contains(fn ($point) => $point['status'] === 'unverified'));
        $this->assertTrue(collect($data)->every(function ($point) {
            return isset($point['category'], $point['latitude'], $point['longitude'], $point['address'], $point['status'])
                && $point['category']['slug'] === 'hospital'
                && $point['type'] === 'hospital';
        }));

        $hospital = MapPoint::query()
            ->where('category_id', $hospitalCategory->id)
            ->where('name', 'Hospital Universitario de Caracas')
            ->first();

        $this->assertNotNull($hospital);
        $this->assertSame('seed', $hospital->source);
        $this->assertSame('unverified', $hospital->status);
        $this->assertSame('hospital', $hospital->type);
    }

    public function test_public_report_is_saved_as_pending(): void
    {
        $response = $this->postJson('/api/public/reports', [
            'report_type' => 'other',
            'title' => 'Hospital sin insumos',
            'description' => 'Se requiere apoyo inmediato.',
            'latitude' => 10.4806,
            'longitude' => -66.9036,
            'address' => 'Centro de Caracas',
            'metadata' => [
                'ui_type' => 'hospital',
                'emergency_available' => false,
            ],
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('citizen_reports', [
            'title' => 'Hospital sin insumos',
            'status' => 'pending',
        ]);
    }

    public function test_login_admin_routes_crud_and_report_conversion_work(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@refumap.test',
            'password' => 'Password123!',
        ]);

        $login->assertOk()->assertJsonPath('user.role', 'admin');
        $token = $login->json('token');

        $this->getJson('/api/admin/dashboard')->assertUnauthorized();

        $dashboard = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard');
        $dashboard->assertOk();

        $hospitalCategory = Category::query()->where('slug', 'hospital')->firstOrFail();

        $createPoint = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/map-points', [
                'category_id' => $hospitalCategory->id,
                'name' => 'Hospital temporal de prueba',
                'address' => 'Caracas',
                'latitude' => 10.49,
                'longitude' => -66.90,
                'status' => 'unverified',
                'source' => 'operator',
            ]);

        $createPoint->assertCreated()->assertJsonPath('data.type', 'hospital');

        $operator = User::query()->where('email', 'operador@refumap.test')->firstOrFail();
        Sanctum::actingAs($operator);

        $report = CitizenReport::query()->create([
            'report_type' => 'new_help_point',
            'title' => 'Nuevo hospital ciudadano',
            'description' => 'Pendiente de conversion',
            'latitude' => 10.47,
            'longitude' => -66.88,
            'address' => 'Caracas',
            'status' => 'pending',
            'metadata' => [
                'emergency_available' => true,
                'needs_supplies' => true,
            ],
        ]);

        $convert = $this->postJson("/api/admin/reports/{$report->id}/convert-to-map-point", [
            'category_id' => $hospitalCategory->id,
            'name' => 'Hospital convertido',
            'status' => 'unverified',
            'source' => 'operator',
        ]);

        $convert->assertCreated()->assertJsonPath('map_point.type', 'hospital');

        $report->refresh();

        $this->assertSame('converted', $report->status);
        $this->assertNotNull($report->converted_map_point_id);
    }
}
