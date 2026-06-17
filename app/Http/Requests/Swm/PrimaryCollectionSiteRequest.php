<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\BuildingInfo\HouseholdRequest;
use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Services\Swm\PrimaryCollectionSiteService;

class PrimaryCollectionSiteRequest extends HouseholdRequest
{
    use MapsValidationAttributes;

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(PrimaryCollectionSiteService::class)->validationAttributeLabels();
    }
}
