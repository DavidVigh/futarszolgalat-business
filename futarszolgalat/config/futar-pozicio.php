<?php

/*
 * Courier positions are owned by the MongoDB team's service; Laravel only reads them.
 * {id} in the endpoint is replaced by the courier id.
 */
return [
    'base_url' => env('FUTAR_POZICIO_URL'),
    'token' => env('FUTAR_POZICIO_TOKEN'),
    'timeout' => (int) env('FUTAR_POZICIO_TIMEOUT', 5),
    'endpoint' => env('FUTAR_POZICIO_ENDPOINT', '/futarok/{id}/pozicio'),
];
