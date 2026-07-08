<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\ModuleSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SwmModuleSettingsService
{
    public const DEFAULT_PER_CAPITA_KG_PER_DAY = 0.52;

    public function get(): ModuleSetting
    {
        return ModuleSetting::query()->firstOrCreate([], [
            'per_capita_sw_generation_kg_per_day' => self::DEFAULT_PER_CAPITA_KG_PER_DAY,
        ]);
    }

    public function perCapitaKgPerDay(): float
    {
        return (float) $this->get()->per_capita_sw_generation_kg_per_day;
    }

    /**
     * Municipality population: active household members.
     */
    public function totalPopulationAsOf(Carbon $asOfEnd): float
    {
        return (float) DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $asOfEnd)
            ->where('status', Household::STATUS_ACTIVE)
            ->sum(DB::raw('COALESCE(number_of_family_members, 0)'));
    }

    public function update(array $data): ModuleSetting
    {
        $setting = $this->get();
        $setting->fill($data);
        $setting->save();

        return $setting;
    }

    /** @return array<string, string> */
    public function validationAttributeLabels(): array
    {
        return [
            'per_capita_sw_generation_kg_per_day' => __('Per Capita Waste Generation (Kg/day)'),
            'description' => __('Description'),
        ];
    }
}
