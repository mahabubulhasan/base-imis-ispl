<?php

namespace Tests\Unit;

use App\Models\Swm\ModuleSetting;
use App\Services\Swm\SwmModuleSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwmModuleSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_per_capita_when_missing(): void
    {
        $service = app(SwmModuleSettingsService::class);
        $setting = $service->get();

        $this->assertInstanceOf(ModuleSetting::class, $setting);
        $this->assertEqualsWithDelta(0.52, $service->perCapitaKgPerDay(), 0.001);
    }

    public function test_update_per_capita(): void
    {
        $service = app(SwmModuleSettingsService::class);
        $service->update(['per_capita_sw_generation_kg_per_day' => 0.75]);

        $this->assertEqualsWithDelta(0.75, $service->perCapitaKgPerDay(), 0.001);
    }
}
