<?php

namespace App\Http\Requests\Legacy;

use App\Models\Futar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FutarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string'],
            'jarmu' => ['required', Rule::in(Futar::JARMUVEK)],
            'hatosugar_km' => ['required', 'integer', 'min:1'],
            'allapot' => ['sometimes', 'string'],
        ];
    }
}
