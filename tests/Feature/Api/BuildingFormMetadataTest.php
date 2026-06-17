<?php

namespace Tests\Feature\Api;

use App\Models\BuildingInfo\Building;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuildingFormMetadataTest extends TestCase
{
    private const FORM_METADATA_KEYS = [
        'ward',
        'structure_type',
        'functional_use',
        'usecatgs_json',
        'water_source',
        'toilet_connection',
        'defecation_place',
        'ctpt',
    ];

    private const LEGACY_METADATA_KEYS = [
        'buildingBin',
        'bin',
        'containment_id',
        'water_source',
        'structure_type',
        'functional_use',
        'usecatgsJson',
        'toiletConnection',
        'defecationPlace',
        'licNames',
        'ctpt',
        'capitalizedctpt',
        'sewer_code',
        'drain_code',
        'waterSupply',
        'use_category_id',
        'containment_type',
        'drain_status',
        'sewer_status',
    ];

    private function authenticateApiUser(): User
    {
        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available in the database.');
        }

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_form_metadata_returns_canonical_keys(): void
    {
        $this->authenticateApiUser();

        $response = $this->getJson('/api/building-info/buildings/form-metadata');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => self::FORM_METADATA_KEYS,
            ]);

        $data = $response->json('data');
        $this->assertSame(self::FORM_METADATA_KEYS, array_keys($data));
        $this->assertIsArray($data['usecatgs_json']);
        $this->assertIsArray($data['ward']);
    }

    public function test_form_metadata_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/buildings/form-metadata');

        $response->assertStatus(401);
    }

    public function test_edit_data_returns_building_and_containment_only(): void
    {
        $this->authenticateApiUser();

        $building = Building::query()->first();

        if (!$building) {
            $this->markTestSkipped('No buildings available in the database.');
        }

        $response = $this->getJson('/api/building-info/buildings/' . $building->bin . '/edit-data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'building',
                    'buildingSurvey',
                    'containment',
                ],
            ]);

        $data = $response->json('data');
        $this->assertSame(['building', 'buildingSurvey', 'containment'], array_keys($data));
        $this->assertNull($data['buildingSurvey']);
        $this->assertIsArray($data['building']);
        $this->assertIsArray($data['containment']);

        foreach (self::LEGACY_METADATA_KEYS as $key) {
            $this->assertArrayNotHasKey($key, $data, "edit-data must not include metadata key: {$key}");
        }

        $this->assertArrayNotHasKey('structure_type', $data);
        $this->assertArrayNotHasKey('ward', $data);
        $this->assertArrayHasKey('bin', $data['building']);
    }

    public function test_edit_data_404_for_unknown_bin(): void
    {
        $this->authenticateApiUser();

        $response = $this->getJson('/api/building-info/buildings/UNKNOWN-BIN-999999/edit-data');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Building not found.',
                'data' => null,
            ]);
    }

    public function test_create_data_still_works_with_deprecation(): void
    {
        $this->authenticateApiUser();

        $response = $this->getJson('/api/building-info/buildings/create-data');

        $response->assertStatus(200)
            ->assertHeader('Deprecation', 'true')
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }
}
