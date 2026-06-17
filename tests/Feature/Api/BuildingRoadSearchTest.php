<?php

namespace Tests\Feature\Api;

use App\Models\UtilityInfo\Roadline;

class BuildingRoadSearchTest extends BuildingSearchApiTestCase
{
    public function test_road_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/roads/search?ward=1');

        $response->assertStatus(401);
    }

    public function test_road_search_requires_ward(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/roads/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ward']);
    }

    public function test_road_search_returns_roads_for_ward(): void
    {
        $this->authenticateApiUser();

        $road = Roadline::query()->whereNotNull('ward')->first();

        if (!$road) {
            $this->markTestSkipped('No roads with ward available in the database.');
        }

        $response = $this->getJson('/api/building-info/roads/search?ward=' . urlencode((string) $road->ward));

        $response->assertStatus(200)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure(['status', 'message', 'data']);

        $data = $response->json('data');
        $this->assertArrayHasKey($road->code, $data);

        $otherWardRoad = Roadline::query()
            ->where('ward', '!=', $road->ward)
            ->value('code');

        if ($otherWardRoad) {
            $this->assertArrayNotHasKey($otherWardRoad, $data);
        }
    }

    public function test_road_search_filters_by_query_within_ward(): void
    {
        $this->authenticateApiUser();

        $road = Roadline::query()->whereNotNull('ward')->first();

        if (!$road) {
            $this->markTestSkipped('No roads with ward available in the database.');
        }

        $query = substr($road->code, 0, 6);

        $response = $this->getJson('/api/building-info/roads/search?ward=' . urlencode((string) $road->ward) . '&q=' . urlencode($query));

        $response->assertStatus(200);
        $this->assertArrayHasKey($road->code, $response->json('data'));
    }
}
