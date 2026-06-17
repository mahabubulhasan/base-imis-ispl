<?php

namespace Tests\Feature\Api;

use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Building;
use App\Models\LayerInfo\Lic;
use App\Models\User;
use App\Models\UtilityInfo\Drain;
use App\Models\UtilityInfo\Roadline;
use App\Models\UtilityInfo\SewerLine;
use App\Models\UtilityInfo\WaterSupplys;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class BuildingSearchApiTestCase extends TestCase
{
    protected function authenticateApiUser(): User
    {
        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available in the database.');
        }

        Sanctum::actingAs($user);

        return $user;
    }
}
