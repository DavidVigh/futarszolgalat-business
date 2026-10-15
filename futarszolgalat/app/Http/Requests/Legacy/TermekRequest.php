<?php

namespace App\Http\Requests\Legacy;

use App\Models\Termek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TermekRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'fogas' => ['required', 'string'],
            'ar' => ['required', 'integer', 'min:0'],
            'tipus' => ['sometimes', Rule::in(Termek::TIPUSOK)],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'elkeszitesi_ido' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
            'allergen_kodok' => ['sometimes', 'array'],
            'allergen_kodok.*' => ['integer', 'distinct', 'min:1'],
        ];
    }
}
