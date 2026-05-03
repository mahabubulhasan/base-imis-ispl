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

    /*
    |--------------------------------------------------------------------------
    | Auto-generated worker_id_no (system Worker ID)
    |--------------------------------------------------------------------------
    |
    | Implemented format: sprintf(prefix_format, organization_id) + zero-padded sequence,
    | e.g. WKR-12-00001 (per-organization sequence).
    |
    | Other patterns you can switch to later (requires code changes):
    | - WKR-{Y}-##### — calendar year + sequence (reset or continuous per year)
    | - WKR-{global_seq} — single increment across all organizations
    | - {org_short_code}-WKR-##### — if organizations carry a short code column
    |
    */

    'worker_id' => [
        'prefix_format' => 'WKR-%d-',
        'sequence_width' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-generated vehicle_id_no (system Vehicle ID)
    |--------------------------------------------------------------------------
    |
    | Per-organization sequence, unique with organization_id when set.
    |
    */

    'vehicle_id' => [
        'prefix_format' => 'VHC-%d-',
        'sequence_width' => 5,
    ],

];
