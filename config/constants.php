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
    'LOGO_URL' => env('LOGO_URL', 'img/stl/logo-Lakshmipur.png'),
    'BACKGROUND_IMAGE_URL' => env('BACKGROUND_IMAGE_URL', 'img/stl/background.png'),
    'SITE_NAME' => env('SITE_NAME', 'Lakshmipur Paurashava'),
];
?>