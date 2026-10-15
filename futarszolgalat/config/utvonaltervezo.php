<?php

return [
    'base_url' => env('UTVONALTERVEZO_URL'),
    'token' => env('UTVONALTERVEZO_TOKEN'),
    'timeout' => (int) env('UTVONALTERVEZO_TIMEOUT', 10),
    'endpoint' => env('UTVONALTERVEZO_ENDPOINT', '/utvonal'),
];
