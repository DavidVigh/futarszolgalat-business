<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFelhasznaloRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'regex:/^(\+36|06)[0-9]{8,9}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:felhasznalo,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'isAdmin' => ['sometimes', 'boolean'],
        ];
    }
}
