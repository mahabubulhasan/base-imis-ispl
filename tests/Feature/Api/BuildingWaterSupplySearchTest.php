<?php

namespace Tests\Feature\Api;

use App\Models\UtilityInfo\WaterSupplys;

class BuildingWaterSupplySearchTest extends BuildingSearchApiTestCase
{
    public function test_water_supply_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/water-supplies/search?road_code=TEST');

        $response->assertStatus(401);
    }

    public function test_water_supply_search_requires_road_code(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/water-supplies/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['road_code']);
    }

    public function test_water_supply_search_returns_records_for_road_code(): void
    {
        $this->authenticateApiUser();

        $waterSupply = WaterSupplys::query()->whereNotNull('road_code')->first();

        if (!$waterSupply) {
            $this->markTestSkipped('No water supply records with road_code available in the database.');
        }

        $response = $this->getJson('/api/building-info/water-supplies/search?road_code=' . urlencode($waterSupply->road_code));

        $response->assertStatus(200)
            ->assertJsonPath('status', 200);

        $data = $response->json('data');
        $this->assertArrayHasKey($waterSupply->code, $data);

        $otherRoadSupply = WaterSupplys::query()
            ->where('road_code', '!=', $waterSupply->road_code)
            ->value('code');

        if ($otherRoadSupply) {
            $this->assertArrayNotHasKey($otherRoadSupply, $data);
        }
    }

    public function test_water_supply_search_filters_by_query(): void
    {
        $this->authenticateApiUser();

        $waterSupply = WaterSupplys::query()->whereNotNull('road_code')->first();

        if (!$waterSupply) {
            $this->markTestSkipped('No water supply records with road_code available in the database.');
        }

        $query = substr($waterSupply->code, 0, 4);

        $response = $this->getJson('/api/building-info/water-supplies/search?road_code=' . urlencode($waterSupply->road_code) . '&q=' . urlencode($query));

        $response->assertStatus(200);
        $this->assertArrayHasKey($waterSupply->code, $response->json('data'));
    }
}
