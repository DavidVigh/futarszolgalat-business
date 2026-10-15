<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFelhasznaloRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'cim' => ['sometimes', 'required', 'string', 'max:255'],
            'telszam' => ['sometimes', 'required', 'string', 'regex:/^(\+36|06)[0-9]{8,9}$/'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('felhasznalo', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'isAdmin' => ['sometimes', 'boolean'],
        ];
    }
}
