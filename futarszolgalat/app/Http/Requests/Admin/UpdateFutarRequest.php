<?php

namespace App\Http\Requests\Admin;

use App\Models\Futar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFutarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'telszam' => ['sometimes', 'required', 'string', 'max:30'],
            'jarmu' => ['sometimes', 'required', Rule::in(Futar::JARMUVEK)],
            'ember' => ['sometimes', 'boolean', 'declined_if:jarmu,dron'],
            'allapot' => ['sometimes', 'required', Rule::in(Futar::ALLAPOTOK)],
            'hatosugar_km' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
