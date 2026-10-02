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
            'telszam' => ['required', 'string', 'max:30'],
            'jarmu' => ['required', Rule::in(Futar::JARMUVEK)],
            // DB CHECK: a drone cannot carry a person
            'ember' => ['required', 'boolean', 'declined_if:jarmu,dron'],
            'allapot' => ['sometimes', Rule::in(Futar::ALLAPOTOK)],
            'hatosugar_km' => ['required', 'integer', 'min:1'],
        ];
    }
}
