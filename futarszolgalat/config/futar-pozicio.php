<?php

/*
 * Courier positions come from the MongoDB team's data through the Python API
 * (GET /futar/{id}/pozicio); Laravel only reads them. Defaults to the Python API's
 * address and token. {id} in the endpoint is replaced by the courier id.
 */
return [
    'base_url' => env('FUTAR_POZICIO_URL', env('PYTHON_API_URL')),
    'token' => env('FUTAR_POZICIO_TOKEN', env('PYTHON_API_TOKEN')),
    'timeout' => (int) env('FUTAR_POZICIO_TIMEOUT', 5),
    'endpoint' => env('FUTAR_POZICIO_ENDPOINT', '/futar/{id}/pozicio'),
];
