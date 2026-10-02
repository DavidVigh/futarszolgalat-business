<?php

namespace App\Http\Requests\Admin;

use App\Models\Rendeles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRendelesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'felhasznalo_id' => ['sometimes', 'required', 'integer', 'exists:felhasznalo,id'],
            'futar_id' => ['sometimes', 'required', 'integer', 'exists:futar,id'],
            'cim' => ['sometimes', 'required', 'string', 'max:255'],
            'prioritas' => ['sometimes', 'boolean'],
            'fizetesi_mod' => ['sometimes', 'required', Rule::in(Rendeles::FIZETESI_MODOK)],
            'jatt' => ['sometimes', 'nullable', 'integer', 'gt:0', 'prohibited_if:fizetesi_mod,keszpenz'],
            'szamla' => ['sometimes', 'boolean'],
            'km' => ['sometimes', 'required', 'integer', 'gt:0'],
            'statusz' => ['sometimes', 'required', Rule::in(Rendeles::STATUSZOK)],
            'ertekeles' => ['sometimes', 'required', 'integer', 'between:1,5'],
        ];
    }
}
