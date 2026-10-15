<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class RendelesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'felhasznalo_id' => ['required', 'integer', 'min:1'],
            'futar_id' => ['required', 'integer', 'min:1'],
            'cim' => ['required', 'string', 'max:255'],
            'fizetesi_mod' => ['required', 'string'],
            'szamla' => ['required', 'integer'],
            'km' => ['required', 'integer', 'min:1'],
            'tetelek' => ['required', 'array', 'min:1'],
            'tetelek.*' => ['required', 'array', 'size:2'],
            'tetelek.*.0' => ['required', 'integer', 'min:1'],
            'tetelek.*.1' => ['required', 'integer', 'min:1'],
            'ertekeles' => ['required', 'integer', 'between:1,5'],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
            'jatt' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'statusz' => ['sometimes', 'string'],
        ];
    }
}
