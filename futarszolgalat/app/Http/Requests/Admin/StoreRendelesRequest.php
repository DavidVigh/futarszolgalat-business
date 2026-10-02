<?php

namespace App\Http\Requests\Admin;

use App\Models\Rendeles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRendelesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'felhasznalo_id' => ['required', 'integer', 'exists:felhasznalo,id'],
            // NOT NULL in the DB: every order needs a courier at creation
            'futar_id' => ['required', 'integer', 'exists:futar,id'],
            'cim' => ['required', 'string', 'max:255'],
            'prioritas' => ['sometimes', 'boolean'],
            'fizetesi_mod' => ['required', Rule::in(Rendeles::FIZETESI_MODOK)],
            // DB CHECK: tip only with card payment
            'jatt' => ['sometimes', 'nullable', 'integer', 'gt:0', 'prohibited_unless:fizetesi_mod,kartya'],
            'szamla' => ['required', 'boolean'],
            'km' => ['required', 'integer', 'gt:0'],
            'statusz' => ['sometimes', Rule::in(Rendeles::STATUSZOK)],
            'ertekeles' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
