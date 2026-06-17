<?php

namespace Tests\Feature\Api;

use App\Models\LayerInfo\Lic;

class BuildingLicSearchTest extends BuildingSearchApiTestCase
{
    public function test_lic_search_requires_auth(): void
    {
        $response = $this->getJson('/api/building-info/lics/search');

        $response->assertStatus(401);
    }

    public function test_lic_search_returns_all_when_no_query(): void
    {
        $this->authenticateApiUser();

        $lic = Lic::query()->whereNull('deleted_at')->first();

        if (!$lic) {
            $this->markTestSkipped('No LIC records available in the database.');
        }

        $response = $this->getJson('/api/building-info/lics/search');

        $response->assertStatus(200)
            ->assertJsonPath('status', 200);

        $data = $response->json('data');
        $this->assertArrayHasKey((string) $lic->id, $data);
        $this->assertSame($lic->community_name, $data[(string) $lic->id]);
    }

    public function test_lic_search_rejects_short_query(): void
    {
        $this->authenticateApiUser();

        $this->getJson('/api/building-info/lics/search?q=ab')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_lic_search_filters_by_community_name(): void
    {
        $this->authenticateApiUser();

        $lic = Lic::query()->whereNull('deleted_at')->whereNotNull('community_name')->first();

        if (!$lic || strlen($lic->community_name) < 3) {
            $this->markTestSkipped('No suitable LIC records available in the database.');
        }

        $query = substr($lic->community_name, 0, 3);

        $response = $this->getJson('/api/building-info/lics/search?q=' . urlencode($query));

        $response->assertStatus(200);
        $this->assertArrayHasKey((string) $lic->id, $response->json('data'));
    }
}
