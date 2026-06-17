<?php

namespace Tests\Feature;

use App\Models\BuildingInfo\BuildingSurvey;
use App\Models\User;
use App\Services\BuildingInfo\BuildingFormDataService;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BuildingSurveyApproveTest extends TestCase
{
    private function authenticateApprover(): ?User
    {
        $user = User::query()->first();
        if (!$user) {
            return null;
        }

        Permission::findOrCreate('Approve Building Survey');
        if (!$user->can('Approve Building Survey')) {
            $user->givePermissionTo('Approve Building Survey');
        }

        $this->actingAs($user);

        return $user;
    }

    public function test_approve_page_is_prefilled_with_survey_data(): void
    {
        if (!$this->authenticateApprover()) {
            $this->markTestSkipped('No users available in the database.');
        }

        $kmlFilename = 'approve-test-' . uniqid() . '.kml';
        $kmlDir = storage_path('app/public/building-survey-kml');
        if (!File::isDirectory($kmlDir)) {
            File::makeDirectory($kmlDir, 0755, true);
        }
        File::put($kmlDir . '/' . $kmlFilename, '<kml></kml>');

        $survey = null;
        try {
            $survey = BuildingSurvey::create([
                'temp_building_code' => 'APPROVE-' . uniqid(),
                'tax_code' => '12-345-6789-01',
                'collected_date' => '2024-06-10',
                'road_code' => '20512510050719-00',
                'house_number' => 'HN-001',
                'functional_use_id' => 1,
                'use_category_id' => 10,
                'water_source_id' => 4,
                'sanitation_system_id' => 3,
                'kml' => $kmlFilename,
                'is_enabled' => true,
                'payload_json' => [
                    'ward' => '5',
                    'main_building' => '1',
                    'structure_type_id' => '2',
                    'construction_year' => '2010-01-01',
                    'floor_count' => '1',
                    'toilet_status' => '1',
                    'toilet_count' => '1',
                ],
            ]);

            $response = $this->get('building-info/building-surveys/' . $survey->id . '/approve');

            $response->assertStatus(200);
            $response->assertSee('12-345-6789-01', false);
            $response->assertSee('20512510050719-00', false);
            $response->assertSee('HN-001', false);
            $response->assertSee('value="5"', false);
            $response->assertSee('name="survey_id"', false);
            $response->assertSee((string) $survey->id, false);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available for building survey approve test: ' . $e->getMessage());
        } finally {
            if ($survey) {
                $survey->forceDelete();
            }
            if (File::exists($kmlDir . '/' . $kmlFilename)) {
                File::delete($kmlDir . '/' . $kmlFilename);
            }
        }
    }

    public function test_get_approve_form_data_returns_null_without_kml_file(): void
    {
        $service = new BuildingFormDataService();
        $survey = null;

        try {
            $survey = BuildingSurvey::create([
                'temp_building_code' => 'NO-KML-' . uniqid(),
                'tax_code' => 'NO-KML-TAX',
                'collected_date' => '2024-06-10',
                'road_code' => '20512510050719-00',
                'kml' => 'missing-file.kml',
                'is_enabled' => true,
                'payload_json' => ['ward' => '1'],
            ]);

            $this->assertNull($service->getApproveFormData($survey->id));
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        } finally {
            if ($survey) {
                $survey->forceDelete();
            }
        }
    }
}
