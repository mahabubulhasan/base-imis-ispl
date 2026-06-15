<?php

namespace Tests\Feature\Api;

use App\Models\UtilityInfo\Drain;

class BuildingDrainSearchTest extends BuildingSearchApiTestCase
{
    public function test_drain_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/drains/search?road_code=TEST');

        $response->assertStatus(401);
    }

    public function test_drain_search_requires_road_code(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/drains/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['road_code']);
    }

    public function test_drain_search_returns_drains_for_road_code(): void
    {
        $this->authenticateApiUser();

        $drain = Drain::query()->whereNotNull('road_code')->first();

        if (!$drain) {
            $this->markTestSkipped('No drains with road_code available in the database.');
        }

        $response = $this->getJson('/api/building-info/drains/search?road_code=' . urlencode($drain->road_code));

        $response->assertStatus(200)
            ->assertJsonPath('status', 200);

        $data = $response->json('data');
        $this->assertArrayHasKey($drain->code, $data);

        $otherRoadDrain = Drain::query()
            ->where('road_code', '!=', $drain->road_code)
            ->value('code');

        if ($otherRoadDrain) {
            $this->assertArrayNotHasKey($otherRoadDrain, $data);
        }
    }

    public function test_drain_search_filters_by_query(): void
    {
        $this->authenticateApiUser();

        $drain = Drain::query()->whereNotNull('road_code')->first();

        if (!$drain) {
            $this->markTestSkipped('No drains with road_code available in the database.');
        }

        $query = substr($drain->code, 0, 4);

        $response = $this->getJson('/api/building-info/drains/search?road_code=' . urlencode($drain->road_code) . '&q=' . urlencode($query));

        $response->assertStatus(200);
        $this->assertArrayHasKey($drain->code, $response->json('data'));
    }
}
