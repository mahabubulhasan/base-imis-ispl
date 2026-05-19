<?php

return [
    'default_to_month' => null,

    'chart_colors' => [
        'yes' => 'rgba(54, 162, 235, 0.75)',
        'no' => 'rgba(251, 176, 64, 0.85)',
    ],

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
        'billing' => [
            'class' => \App\Services\Swm\Dashboard\Modules\BillingDashboardModule::class,
            'view' => 'swm.dashboard.modules.billing',
            'permission' => null,
            'enabled' => true,
        ],
        'complaints' => [
            'class' => \App\Services\Swm\Dashboard\Modules\ComplaintsDashboardModule::class,
            'view' => 'swm.dashboard.modules.complaints',
            'permission' => null,
            'enabled' => true,
        ],
    ],
];
