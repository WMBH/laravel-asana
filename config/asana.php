<?php

// config for WMBH/Asana
return [
    'token' => env('ASANA_TOKEN'),

    'timeout' => env('ASANA_TIMEOUT', 30),

    'retry' => [
        'attempts' => env('ASANA_RETRY_ATTEMPTS', 3),
        'sleep' => env('ASANA_RETRY_SLEEP', 1000),
    ],
];
