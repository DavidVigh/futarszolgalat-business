<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminFelhasznaloUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->getKey() : $user;

        return [
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'cim' => ['sometimes', 'required', 'string', 'max:255'],
            'telszam' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('felhasznalo', 'telszam')->ignore($userId),
            ],
            'jelszo' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'adminE' => ['sometimes', 'boolean'],
        ];
    }
}
