<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllergenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Unique regardless of case: the column is COLLATE NOCASE (BL 9)
            'nev' => ['required', 'string', 'max:255', Rule::unique('allergen', 'nev')->ignore($this->route('allergen'), 'kod')],
        ];
    }
}
