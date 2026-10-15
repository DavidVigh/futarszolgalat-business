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
            'jatt' => ['sometimes', 'nullable', 'integer', 'min:1', 'prohibited_unless:fizetesi_mod,kartya'],
            'szamla' => ['sometimes', 'boolean'],
            'tetelek' => ['required', 'array', 'min:1'],
            'tetelek.*.termek_id' => ['required', 'integer', Rule::exists('termek', 'id')->where('aktiv', 1)],
            'tetelek.*.db' => ['required', 'integer', 'min:1'],
        ];
    }
}
