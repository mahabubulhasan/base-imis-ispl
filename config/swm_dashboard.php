<?php

return [
    'default_to_month' => null,

    'modules' => [
        'households' => [
            'class' => \App\Services\Swm\Dashboard\Modules\HouseholdDashboardModule::class,
            'view' => 'swm.dashboard.modules.households',
            'permission' => null,
            'enabled' => true,
        ],
        'service_providers' => [
            'class' => \App\Services\Swm\Dashboard\Modules\ServiceProvidersDashboardModule::class,
            'view' => 'swm.dashboard.modules.service-providers',
            'permission' => null,
            'enabled' => true,
        ],
        'service_facilities' => [
            'class' => \App\Services\Swm\Dashboard\Modules\ServiceFacilitiesDashboardModule::class,
            'view' => 'swm.dashboard.modules.service-facilities',
            'permission' => null,
            'enabled' => true,
        ],
        'service_management' => [
            'class' => \App\Services\Swm\Dashboard\Modules\ServiceManagementDashboardModule::class,
            'view' => 'swm.dashboard.modules.service-management',
            'permission' => null,
            'enabled' => true,
        ],
    ],
];
