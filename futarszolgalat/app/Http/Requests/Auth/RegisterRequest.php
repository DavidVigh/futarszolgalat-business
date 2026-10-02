<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:felhasznalo,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
