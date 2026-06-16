<?php

namespace App\Support\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Models\User;

class SwmImportTemplateOptions
{
    /** @return array<int, string> */
    public static function wardNumberStrings(): array
    {
        return array_map('strval', array_keys(Ward::getInAscOrder()));
    }

    /** @return array<int, string> */
    public static function wasteTypeNames(): array
    {
        return WasteType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /** @return array<int, string> */
    public static function stsLabels(): array
    {
        return Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Sts $sts) => trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name))
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public static function landfillLabels(): array
    {
        return Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Landfill $landfill) => trim(($landfill->landfill_id ? $landfill->landfill_id.' - ' : '').$landfill->name))
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public static function activeHouseholdDbIds(): array
    {
        return Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /** @return array<int, string> */
    public static function householdCustomerLabels(): array
    {
        return Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->orderBy('household_id')
            ->get(['id', 'household_id'])
            ->map(fn (Household $h) => trim($h->household_id).' - '.$h->id)
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public static function userLabels(): array
    {
        return User::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => trim($user->name).' - '.$user->id)
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public static function yesNo(): array
    {
        return [__('Yes'), __('No')];
    }

    /** @return array<int, string> */
    public static function priorityLevels(): array
    {
        return ['1', '2', '3', '4', '5'];
    }
}
