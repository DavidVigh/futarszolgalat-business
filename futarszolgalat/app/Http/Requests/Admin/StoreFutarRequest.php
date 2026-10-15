<?php

namespace App\Http\Requests\Admin;

use App\Models\Futar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFutarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'regex:/^(\+36|06)[0-9]{8,9}$/', 'unique:futar,telszam'],
            'jarmu' => ['required', Rule::in(Futar::JARMUVEK)],
            // A drone cannot carry a person (BL 3)
            'ember' => ['sometimes', 'boolean', 'declined_if:jarmu,dron'],
            'hatosugar_km' => ['required', 'integer', 'min:1'],
        ];
    }
}
