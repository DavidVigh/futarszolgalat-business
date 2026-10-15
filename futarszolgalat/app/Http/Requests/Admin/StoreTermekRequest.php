<?php

namespace App\Http\Requests\Admin;

use App\Models\Termek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTermekRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tipus' => ['required', Rule::in(Termek::TIPUSOK)],
            'nev' => ['required', 'string', 'max:255'],
            // The (fogas, tipus) pair must exist in the fogas table (fk_termek_fogas)
            'fogas' => ['required', 'string', Rule::exists('fogas', 'kod')->where('tipus', $this->input('tipus'))],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'ar' => ['required', 'integer', 'min:0'],
            // Required and positive for food, optional for drinks (BL 7)
            'elkeszitesi_ido' => ['required_if:tipus,etel', 'nullable', 'integer', 'min:1'],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
            'aktiv' => ['sometimes', 'boolean'],
            'allergen_ellenorizve' => ['sometimes', 'boolean'],
            'allergenek' => ['sometimes', 'array'],
            'allergenek.*' => ['integer', 'distinct', 'exists:allergen,kod'],
        ];
    }
}
