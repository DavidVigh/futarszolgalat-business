<?php

namespace App\Http\Requests;

use App\Models\Rendeles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRendelesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Defaults to the customer's own address (BL 5)
            'cim' => ['sometimes', 'nullable', 'string', 'max:255'],
            'km' => ['required', 'integer', 'min:1'],
            'prioritas' => ['sometimes', 'boolean'],
            'fizetesi_mod' => ['required', Rule::in(Rendeles::FIZETESI_MODOK)],
            // A tip can only be given after delivery: POST /rendelesek/{rendeles}/jatt
            'jatt' => ['prohibited'],
            'szamla' => ['sometimes', 'boolean'],
            // Defaults to the customer's name and address when an invoice is requested
            'szamlazasi_adat' => ['sometimes', 'array', Rule::prohibitedIf(! $this->boolean('szamla'))],
            'szamlazasi_adat.nev' => ['sometimes', 'string', 'max:255'],
            'szamlazasi_adat.cim' => ['sometimes', 'string', 'max:255'],
            'szamlazasi_adat.adoszam' => ['sometimes', 'nullable', 'regex:/^\d{8}-\d-\d{2}$/'],
            'tetelek' => ['required', 'array', 'min:1'],
            'tetelek.*.termek_id' => ['required', 'integer', Rule::exists('termek', 'id')->where('aktiv', 1)],
            'tetelek.*.db' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'jatt.prohibited' => 'Jatt csak a kiszallitas utan adhato.',
            'szamlazasi_adat.adoszam.regex' => 'Az adoszam formatuma 12345678-1-12.',
        ];
    }
}
