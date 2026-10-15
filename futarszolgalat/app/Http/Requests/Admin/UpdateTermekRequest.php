<?php

namespace App\Http\Requests\Admin;

use App\Models\Termek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTermekRequest extends FormRequest
{
    public function rules(): array
    {
        $tipus = $this->input('tipus', $this->route('termek')?->tipus);

        return [
            'tipus' => ['sometimes', 'required', Rule::in(Termek::TIPUSOK)],
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'fogas' => [
                Rule::requiredIf($this->has('tipus')),
                $tipus === 'ital' ? Rule::in(Termek::ITAL_FOGASOK) : Rule::notIn(Termek::ITAL_FOGASOK),
                Rule::in(Termek::FOGASOK),
            ],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'ar' => ['sometimes', 'required', 'integer', 'min:0'],
            // Required for food: when sent, or when the product becomes food without one (BL 7)
            'elkeszitesi_ido' => [
                Rule::requiredIf($tipus === 'etel' && ($this->has('elkeszitesi_ido') || $this->route('termek')?->elkeszitesi_ido === null)),
                'nullable',
                'integer',
                'min:1',
            ],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
            'aktiv' => ['sometimes', 'boolean'],
            'allergenek' => ['sometimes', 'array'],
            'allergenek.*' => ['integer', 'distinct', 'exists:allergen,kod'],
        ];
    }
}
