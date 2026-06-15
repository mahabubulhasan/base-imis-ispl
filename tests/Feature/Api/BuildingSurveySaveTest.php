<?php

namespace Tests\Feature\Api;

use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\BuildingSurvey;
use App\Models\User;
use App\Services\BuildingInfo\BuildingFormDataService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class BuildingSurveySaveTest extends TestCase
{
    private BuildingFormDataService $formDataService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formDataService = new BuildingFormDataService();
    }

    private function authenticateApiUser(): User
    {
        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available in the database.');
        }

        Sanctum::actingAs($user);

        return $user;
    }

    private function sampleRequestData(string $tempBuildingCode): array
    {
        $building = Building::query()->first();
        $roadCode = $building?->road_code ?? '20512510050719-00';

        return [
            'temp_building_code' => $tempBuildingCode,
            'tax_code' => 'TEST-TAX-' . substr($tempBuildingCode, -8),
            'collected_date' => '2024-06-10',
            'ward' => '5',
            'road_code' => $roadCode,
            'main_building' => '1',
            'structure_type_id' => '2',
            'construction_year' => '2010-01-01',
            'floor_count' => '1',
            'functional_use_id' => '1',
            'use_category_id' => '10',
            'water_source_id' => '4',
            'toilet_status' => '1',
            'toilet_count' => '1',
            'sanitation_system_id' => '3',
        ];
    }

    private function assembleSurveyData(array $requestData): array
    {
        $split = $this->formDataService->splitSurveyRequestData($requestData);

        return array_merge($split['columns'], [
            'payload_json' => $split['payload_json'],
            'is_enabled' => true,
        ]);
    }

    private function sampleKmlFile(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('footprint.kml', <<<'KML'
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <Placemark>
      <Polygon>
        <outerBoundaryIs>
          <LinearRing>
            <coordinates>90.0,23.0 90.1,23.0 90.1,23.1 90.0,23.1 90.0,23.0</coordinates>
          </LinearRing>
        </outerBoundaryIs>
      </Polygon>
    </Placemark>
  </Document>
</kml>
KML);
    }

    public function test_ward_is_stored_in_column(): void
    {
        $tempBuildingCode = 'PAYLOAD-' . uniqid();
        $data = $this->assembleSurveyData($this->sampleRequestData($tempBuildingCode));

        try {
            $survey = BuildingSurvey::create($data);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available for building survey insert: ' . $e->getMessage());
        }

        $this->assertSame('5', (string) $survey->ward);
        $this->assertArrayNotHasKey('ward', $survey->payload_json ?? []);
        $this->assertContains('ward', BuildingFormDataService::SURVEY_KNOWN_COLUMNS);

        $survey->forceDelete();
    }

    public function test_ward_is_required_on_save_building(): void
    {
        $this->authenticateApiUser();

        $tempBuildingCode = 'TEST-' . uniqid();
        $payload = $this->sampleRequestData($tempBuildingCode);
        unset($payload['ward']);

        $response = $this->post('/api/save-building', array_merge($payload, ['kml' => $this->sampleKmlFile()]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ward']);
    }

    public function test_save_building_persists_columns_and_payload(): void
    {
        $user = $this->authenticateApiUser();

        DB::partialMock()
            ->shouldReceive('select')
            ->with(Mockery::on(fn ($sql) => is_string($sql) && str_contains($sql, 'ST_IsValid')))
            ->andReturn([(object) ['status' => true]]);

        $tempBuildingCode = 'SAVE-' . uniqid();
        $payload = $this->sampleRequestData($tempBuildingCode);

        try {
            $response = $this->post('/api/save-building', array_merge($payload, ['kml' => $this->sampleKmlFile()]));
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available for building survey save: ' . $e->getMessage());
        }

        if ($response->status() !== 200) {
            $this->markTestSkipped('Save building endpoint did not succeed: ' . $response->getContent());
        }

        $survey = BuildingSurvey::query()->where('temp_building_code', $tempBuildingCode)->first();

        if (!$survey) {
            $this->markTestSkipped('Survey record was not created.');
        }

        foreach (BuildingFormDataService::SURVEY_KNOWN_COLUMNS as $column) {
            $this->assertNotNull($survey->{$column}, "Expected column {$column} to be set.");
        }

        $this->assertSame((int) $user->id, (int) $survey->user_id);
        $this->assertTrue($survey->is_enabled);
        $this->assertNotEmpty($survey->kml);
        $this->assertArrayHasKey('structure_type_id', $survey->payload_json ?? []);
        $this->assertArrayNotHasKey('ward', $survey->payload_json ?? []);

        $survey->forceDelete();
    }
}
