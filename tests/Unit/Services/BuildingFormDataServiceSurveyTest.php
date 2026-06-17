<?php

namespace Tests\Unit\Services;

use App\Models\BuildingInfo\BuildingSurvey;
use App\Services\BuildingInfo\BuildingFormDataService;
use Carbon\Carbon;
use Tests\TestCase;

class BuildingFormDataServiceSurveyTest extends TestCase
{
    private BuildingFormDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BuildingFormDataService();
    }

    private function makeSurvey(array $attributes = []): BuildingSurvey
    {
        $survey = new BuildingSurvey();
        $survey->forceFill(array_merge([
            'id' => 1,
            'collected_date' => Carbon::parse('2024-06-10'),
            'payload_json' => [],
        ], $attributes));

        return $survey;
    }

    public function test_column_values_override_payload_json(): void
    {
        $survey = $this->makeSurvey([
            'tax_code' => 'COLUMN-TAX',
            'road_code' => 'ROAD-COL',
            'payload_json' => [
                'tax_code' => 'PAYLOAD-TAX',
                'road_code' => 'ROAD-PAY',
                'ward' => '7',
            ],
        ]);

        $data = $this->service->flattenSurveyToFormData($survey);

        $this->assertSame('COLUMN-TAX', $data['tax_code']);
        $this->assertSame('ROAD-COL', $data['road_code']);
        $this->assertSame('7', $data['ward']);
    }

    public function test_ward_from_payload_when_not_in_column(): void
    {
        $survey = $this->makeSurvey([
            'payload_json' => ['ward' => '5'],
        ]);

        $data = $this->service->flattenSurveyToFormData($survey);

        $this->assertSame('5', (string) $data['ward']);
    }

    public function test_nested_payload_json_is_unwrapped(): void
    {
        $survey = $this->makeSurvey([
            'payload_json' => [
                'payload_json' => [
                    'structure_type_id' => '2',
                    'floor_count' => '1',
                ],
                'ward' => '3',
            ],
        ]);

        $data = $this->service->flattenSurveyToFormData($survey);

        $this->assertSame('2', (string) $data['structure_type_id']);
        $this->assertSame('1', (string) $data['floor_count']);
        $this->assertSame('3', (string) $data['ward']);
    }

    public function test_toilet_absent_maps_sanitation_system_to_defecation_place(): void
    {
        $survey = $this->makeSurvey([
            'sanitation_system_id' => 9,
            'payload_json' => [
                'toilet_status' => '0',
                'structure_type_id' => '2',
                'construction_year' => '2010-01-01',
                'floor_count' => '1',
            ],
        ]);

        $form = $this->service->buildSurveyFormData($survey);

        $this->assertFalse($form->toilet_status);
        $this->assertSame(9, $form->defecation_place);
    }

    public function test_surveyed_date_defaults_to_collected_date(): void
    {
        $survey = $this->makeSurvey([
            'collected_date' => Carbon::parse('2024-06-10'),
            'payload_json' => [
                'structure_type_id' => '2',
            ],
        ]);

        $form = $this->service->buildSurveyFormData($survey);

        $this->assertSame('2024-06-10', $form->surveyed_date);
    }

    public function test_main_building_inferred_from_associated_bin(): void
    {
        $survey = $this->makeSurvey([
            'payload_json' => [
                'building_associated_to' => 'BIN-001',
            ],
        ]);

        $form = $this->service->buildSurveyFormData($survey);

        $this->assertFalse($form->main_building);
        $this->assertSame('BIN-001', $form->building_associated_to);
    }

    public function test_lic_id_sets_low_income_hh_to_yes(): void
    {
        $survey = $this->makeSurvey([
            'payload_json' => [
                'lic_id' => 12,
                'low_income_hh' => '0',
            ],
        ]);

        $form = $this->service->buildSurveyFormData($survey);

        $this->assertTrue($form->low_income_hh);
        $this->assertSame('1', $form->lic_status);
    }

    public function test_lic_status_derived_from_lic_id(): void
    {
        $survey = $this->makeSurvey([
            'payload_json' => [
                'lic_id' => 12,
            ],
        ]);

        $form = $this->service->buildSurveyFormData($survey);

        $this->assertSame('1', $form->lic_status);
    }

    public function test_split_survey_request_data_stores_ward_in_column(): void
    {
        $split = $this->service->splitSurveyRequestData([
            'temp_building_code' => 'BIN-001',
            'tax_code' => 'TAX-001',
            'collected_date' => '2024-06-10',
            'ward' => '5',
            'road_code' => 'ROAD-001',
            'functional_use_id' => '1',
            'structure_type_id' => '2',
        ]);

        $this->assertSame('5', $split['columns']['ward']);
        $this->assertArrayNotHasKey('ward', $split['payload_json']);
        $this->assertSame('2', (string) $split['payload_json']['structure_type_id']);
    }

    public function test_split_survey_request_data_unwraps_nested_payload_json(): void
    {
        $split = $this->service->splitSurveyRequestData([
            'temp_building_code' => 'BIN-002',
            'tax_code' => 'TAX-002',
            'collected_date' => '2024-06-10',
            'road_code' => 'ROAD-002',
            'functional_use_id' => '1',
            'payload_json' => [
                'payload_json' => [
                    'structure_type_id' => '3',
                    'floor_count' => '2',
                ],
                'ward' => '7',
            ],
        ]);

        $this->assertSame('7', $split['columns']['ward']);
        $this->assertSame('3', (string) $split['payload_json']['structure_type_id']);
        $this->assertSame('2', (string) $split['payload_json']['floor_count']);
        $this->assertArrayNotHasKey('payload_json', $split['payload_json']);
    }

    public function test_split_survey_request_data_strips_duplicate_known_columns_from_payload(): void
    {
        $split = $this->service->splitSurveyRequestData([
            'temp_building_code' => 'BIN-003',
            'tax_code' => 'TAX-003',
            'collected_date' => '2024-06-10',
            'ward' => '4',
            'road_code' => 'ROAD-003',
            'functional_use_id' => '1',
            'payload_json' => [
                'ward' => '9',
                'road_code' => 'ROAD-DUP',
                'structure_type_id' => '2',
            ],
        ]);

        $this->assertSame('4', $split['columns']['ward']);
        $this->assertSame('ROAD-003', $split['columns']['road_code']);
        $this->assertArrayNotHasKey('ward', $split['payload_json']);
        $this->assertArrayNotHasKey('road_code', $split['payload_json']);
    }

    public function test_split_survey_request_data_excludes_file_fields(): void
    {
        $split = $this->service->splitSurveyRequestData([
            'temp_building_code' => 'BIN-004',
            'tax_code' => 'TAX-004',
            'collected_date' => '2024-06-10',
            'ward' => '1',
            'road_code' => 'ROAD-004',
            'functional_use_id' => '1',
            'kml' => 'fake-kml-content',
            'house_image' => 'fake-image-content',
            'structure_type_id' => '2',
        ]);

        $this->assertArrayNotHasKey('kml', $split['payload_json']);
        $this->assertArrayNotHasKey('house_image', $split['payload_json']);
    }
}
