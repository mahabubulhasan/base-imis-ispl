<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver work type name
    |--------------------------------------------------------------------------
    |
    | Workers with this work type (case-insensitive match on swm.work_types.name)
    | are eligible as vehicle drivers. Seed via Database\Seeders\Swm\WorkTypeSeeder.
    |
    */

    'driver_work_type_name' => env('SWM_DRIVER_WORK_TYPE_NAME', 'Driver'),

];
