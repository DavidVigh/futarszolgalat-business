<?php

namespace App\Http\Requests\Admin;

use App\Models\Termek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTermekRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tipus' => ['sometimes', 'required', Rule::in(Termek::TIPUSOK)],
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'fogas' => ['sometimes', 'required', Rule::in(Termek::FOGASOK)],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'ar' => ['sometimes', 'required', 'integer', 'min:0'],
            'elkeszitesi_ido' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
