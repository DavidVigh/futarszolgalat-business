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
            'fogas' => ['required', Rule::in(Termek::FOGASOK)],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'ar' => ['required', 'integer', 'min:0'],
            'elkeszitesi_ido' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'prioritas' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
