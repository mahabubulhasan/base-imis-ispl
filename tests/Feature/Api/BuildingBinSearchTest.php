<?php

namespace Tests\Feature\Api;

use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Building;

class BuildingBinSearchTest extends BuildingSearchApiTestCase
{
    public function test_bin_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/buildings/bins/search?q=B&type=building_bin');

        $response->assertStatus(401);
    }

    public function test_bin_search_validates_required_fields(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/buildings/bins/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q', 'type']);
    }

    public function test_bin_search_requires_minimum_query_length(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/buildings/bins/search?q=&type=building_bin')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_building_bin_search_returns_main_buildings_only(): void
    {
        $this->authenticateApiUser();

        $building = Building::query()
            ->whereNull('building_associated_to')
            ->whereNull('deleted_at')
            ->first();

        if (!$building) {
            $this->markTestSkipped('No main buildings available in the database.');
        }

        $prefix = substr($building->bin, 0, 3);

        $response = $this->getJson('/api/building-info/buildings/bins/search?q=' . urlencode($prefix) . '&type=building_bin');

        $response->assertStatus(200)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure(['status', 'message', 'data']);

        $data = $response->json('data');
        $this->assertArrayHasKey($building->bin, $data);

        $associated = Building::query()
            ->whereNotNull('building_associated_to')
            ->where('bin', 'ilike', $prefix . '%')
            ->whereNull('deleted_at')
            ->value('bin');

        if ($associated) {
            $this->assertArrayNotHasKey($associated, $data);
        }
    }

    public function test_preconnected_bin_search_returns_results(): void
    {
        $this->authenticateApiUser();

        $buildContain = BuildContain::query()
            ->whereNull('deleted_at')
            ->first();

        if (!$buildContain) {
            $this->markTestSkipped('No build contain records available in the database.');
        }

        $prefix = substr($buildContain->bin, 0, 3);

        $response = $this->getJson('/api/building-info/buildings/bins/search?q=' . urlencode($prefix) . '&type=preconnected_bin');

        $response->assertStatus(200);
        $this->assertArrayHasKey($buildContain->bin, $response->json('data'));
    }
}
