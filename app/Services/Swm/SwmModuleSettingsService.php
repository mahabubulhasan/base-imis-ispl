<?php

namespace App\Services\Swm;

use App\Models\Swm\ModuleSetting;

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

    public function update(array $data): ModuleSetting
    {
        $setting = $this->get();
        $setting->fill($data);
        $setting->save();

        return $setting;
    }
}
