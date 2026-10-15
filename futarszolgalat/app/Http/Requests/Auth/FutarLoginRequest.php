<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class FutarLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30'],
        ];
    }
}
