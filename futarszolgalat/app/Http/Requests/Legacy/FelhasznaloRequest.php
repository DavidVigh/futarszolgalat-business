<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class FelhasznaloRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string'],
            'email' => ['required', 'email'],
            'jelszo' => ['required', 'string', 'min:8'],
            'admin' => ['sometimes', 'boolean'],
        ];
    }
}
