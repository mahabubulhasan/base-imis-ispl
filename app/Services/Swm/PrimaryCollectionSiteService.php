<?php

namespace App\Services\Swm;

use App\Services\BuildingInfo\HouseholdService;

class PrimaryCollectionSiteService extends HouseholdService
{
    // Backward-compatible alias; use BuildingInfo\HouseholdService directly.

    /** @return array<string, string> */
    public function validationAttributeLabels(): array
    {
        return [
            'customer_id' => __('Customer ID'),
            'customer_name' => __('Customer Name'),
            'contact_number' => __('Contact Number'),
            'area_mohalla_name' => __('Area / Mohalla Name'),
            'bin' => __('BIN'),
            'ward' => __('Ward'),
            'road_no' => __('Road No.'),
            'road_name' => __('Road Name'),
            'holding_number' => __('Holding Number'),
            'tax_id' => __('Tax ID'),
            'functional_use' => __('Functional Use'),
            'waste_charge' => __('Waste Charge'),
            'number_of_family_members' => __('Number of Family Members'),
            'using_this_service_since' => __('Using This Service Since'),
            'daily_waste_volume' => __('Total volume of the waste collected (daily average approx.)'),
            'remarks' => __('Remarks'),
            'survey_date' => __('Survey Date'),
            'van_puller_id' => __('Van Puller'),
            'is_owner' => __('Owner'),
            'is_lic' => __('LIC'),
            'lic_id' => __('LIC ID (if Y)'),
            'segregation_practiced' => __('Segregation Practiced'),
            'waste_bin_provided' => __('Waste bin Provided'),
        ];
    }
}
