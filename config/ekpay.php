<?php

return [
    'MERCHANT_REG_ID' => env('MERCHANT_REG_ID', 'lakshmipur_pouro_imis'),
    'MERCHANT_PAS_KEY' => env('MERCHANT_PAS_KEY', 'lD8@sduj'),
    'SANDBOX_ENABLED' => env('SANDBOX_ENABLED', true),
    'MAC' => env('MAC', '1.1.1.1'), // for production it should be the IP of the server where the application is hosted
    'IPN_EMAIL' => env('IPN_EMAIL', 'codehasan@gmail.com'),
    'DEFAULT_AMOUNT' => env('DEFAULT_AMOUNT', 1500),
];
