<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FelhasznaloStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30', 'unique:felhasznalo,telszam'],
            'jelszo' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
