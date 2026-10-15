<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class FelhasznaloUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['sometimes', 'string', 'max:255'],
            'cim' => ['sometimes', 'string', 'max:255'],
            'telszam' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email'],
            'jelszo' => ['sometimes', 'string', 'min:8'],
            'admin' => ['sometimes', 'boolean'],
        ];
    }
}
