<?php

return [
    'key' => env('INTUIT_KEY'),
    'secret' => env('INTUIT_SECRET'),
    'redirect' => env('INTUIT_REDIRECT_URL'),
    'environment' => env('INTUIT_ENVIRONMENT','sandbox'),
    'base_url' => env('INTUIT_BASE_URL'),
];