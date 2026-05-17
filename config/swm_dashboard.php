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
    ],
];
