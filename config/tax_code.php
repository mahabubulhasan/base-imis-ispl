<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tax Code / Holding ID — renderer & validation mode
    |--------------------------------------------------------------------------
    |
    | Drives BOTH the building form field renderer and the server-side
    | validation in App\Http\Requests\BuildingInfo\BuildingRequest so the two
    | can never drift.
    |
    |   'regex'  : single free-text input, filtered/validated by 'allowed_chars'
    |   'legacy' : tag-list UI; each tag must match 'legacy_format'
    |
    | NOTE: if config is cached on the server, run `php artisan config:clear`
    | after changing any TAX_CODE_* value in .env.
    |
    */
    'mode' => env('TAX_CODE_MODE', 'regex'),

    'max_length' => env('TAX_CODE_MAX_LENGTH', 250),

    /*
    | regex mode — single source of truth for the allowed character set.
    | Expressed as a regex character-class body (no surrounding [ ]).
    | PHP builds  /^[<body>]*$/   ; JS builds  /[^<body>]/g  for live filtering.
    | Default allows: digits, letters, comma, - / and all brackets () [] {} <>
    */
    'allowed_chars' => env('TAX_CODE_ALLOWED_CHARS', '0-9a-zA-Z,\-\/\[\]{}()<>'),

    /*
    | legacy mode — per-code structural format (no anchors/delimiters).
    | One or more of these may be entered, joined by commas.
    | Default: XX-XXX-XXXX-XX with the last group allowing trailing 'x'.
    */
    'legacy_format' => env('TAX_CODE_LEGACY_FORMAT', '\d{2}-\d{3}-\d{4}-[0-9x]{2}'),

];
