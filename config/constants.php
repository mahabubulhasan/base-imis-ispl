<?php

return [
    'GEOSERVER_WORKSPACE' => env('GEOSERVER_WORKSPACE'),
    'GEOSERVER' => env('GEOSERVER'),
    'GEOSERVER_URL' => env('GEOSERVER_URL'),
    'PGSQL_BIN_PATH' => env('PGSQL_BIN_PATH'),
    'AUTH_KEY' => env('AUTH_KEY'),
    'GEOSERVER_USERNAME' => env('GEOSERVER_USERNAME'),
    'GEOSERVER_AUTH_PROXY' => env('GEOSERVER_AUTH_PROXY'),
    # BASE MAP API KEYS
    'API_KEY_BING' => env('API_KEY_BING'),
    'API_KEY_GOOGLE' => env('API_KEY_GOOGLE'),
    # Deployment Site Information
    'LOGO_URL' => env('LOGO_URL', 'img/stl/logo-chapainawabganj.png'),
    'FAVICON_URL' => env('FAVICON_URL', 'img/stl/logo-chapainawabganj.png'),
    'BACKGROUND_IMAGE_URL' => env('BACKGROUND_IMAGE_URL', 'img/stl/IMIS_City_Login_Final_Background.png'),
    'SITE_NAME' => env('SITE_NAME', 'Chapainawabganj Paurashava'),
    'ROAD_TYPES' => [
        'NationalHighway' => ['name' => 'National Highway', 'bn_name' => 'জাতীয় মহাসড়ক', 'is_default' => false],
        'RegionalHighway' => ['name' => 'Regional Highway', 'bn_name' => 'আঞ্চলিক মহাসড়ক', 'is_default' => false],
        'ZillaRoad' => ['name' => 'Zilla Road', 'bn_name' => 'জেলা সড়ক', 'is_default' => false],
        'MunicipalityRoad' => ['name' => 'Municipality Road', 'bn_name' => 'পৌরসভা সড়ক', 'is_default' => true],
    ],
];