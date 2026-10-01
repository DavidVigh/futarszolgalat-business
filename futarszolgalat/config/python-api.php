<?php

return [
    'base_url' => env('PYTHON_API_URL'),
    'token' => env('PYTHON_API_TOKEN'),
    'timeout' => (int) env('PYTHON_API_TIMEOUT', 10),
    'endpoints' => [
        'felhasznalo_hozzaadasa' => env('PYTHON_API_FELHASZNALO_HOZZAADASA', '/felhasznalo_hozzaadasa'),
        'felhasznalo_modositasa' => env('PYTHON_API_FELHASZNALO_MODOSITASA', '/felhasznalo_modositasa'),
        'felhasznalo_torlese' => env('PYTHON_API_FELHASZNALO_TORLESE', '/felhasznalo_torlese'),
        'futar_hozzaadasa' => env('PYTHON_API_FUTAR_HOZZAADASA', '/futar_hozzaadasa'),
        'futar_modositasa' => env('PYTHON_API_FUTAR_MODOSITASA', '/futar_modositasa'),
        'futar_torlese' => env('PYTHON_API_FUTAR_TORLESE', '/futar_torlese'),
        'termek_hozzaadasa' => env('PYTHON_API_TERMEK_HOZZAADASA', '/termek_hozzaadasa'),
        'termek_modositasa' => env('PYTHON_API_TERMEK_MODOSITASA', '/termek_modositasa'),
        'termek_torlese' => env('PYTHON_API_TERMEK_TORLESE', '/termek_torlese'),
        'rendeles_hozzaadasa' => env('PYTHON_API_RENDELES_HOZZAADASA', '/rendeles_hozzaadasa'),
        'rendeles_modositasa' => env('PYTHON_API_RENDELES_MODOSITASA', '/rendeles_modositasa'),
        'rendeles_torlese' => env('PYTHON_API_RENDELES_TORLESE', '/rendeles_torlese'),
    ],
];
