<?php

namespace Tests\Feature\Api;

use App\Models\UtilityInfo\SewerLine;

class BuildingSewerSearchTest extends BuildingSearchApiTestCase
{
    public function test_sewer_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/sewers/search?road_code=TEST');

        $response->assertStatus(401);
    }

    public function test_sewer_search_requires_road_code(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/sewers/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['road_code']);
    }

    public function test_sewer_search_returns_sewers_for_road_code(): void
    {
        $this->authenticateApiUser();

        $sewer = SewerLine::query()->whereNotNull('road_code')->first();

        if (!$sewer) {
            $this->markTestSkipped('No sewers with road_code available in the database.');
        }

        $response = $this->getJson('/api/building-info/sewers/search?road_code=' . urlencode($sewer->road_code));

        $response->assertStatus(200)
            ->assertJsonPath('status', 200);

        $data = $response->json('data');
        $this->assertArrayHasKey($sewer->code, $data);

        $otherRoadSewer = SewerLine::query()
            ->where('road_code', '!=', $sewer->road_code)
            ->value('code');

        if ($otherRoadSewer) {
            $this->assertArrayNotHasKey($otherRoadSewer, $data);
        }
    }

    public function test_sewer_search_filters_by_query(): void
    {
        $this->authenticateApiUser();

        $sewer = SewerLine::query()->whereNotNull('road_code')->first();

        if (!$sewer) {
            $this->markTestSkipped('No sewers with road_code available in the database.');
        }

        $query = substr($sewer->code, 0, 4);

        $response = $this->getJson('/api/building-info/sewers/search?road_code=' . urlencode($sewer->road_code) . '&q=' . urlencode($query));

        $response->assertStatus(200);
        $this->assertArrayHasKey($sewer->code, $response->json('data'));
    }
}
