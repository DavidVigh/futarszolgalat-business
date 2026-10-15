<?php

namespace App\Http\Requests\Admin;

use App\Models\Futar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The courier status (allapot) is not editable here: it only changes through
 * the order flow and the courier's own elerheto/szunet switch (BL 10).
 */
class UpdateFutarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'telszam' => ['sometimes', 'required', 'string', 'regex:/^(\+36|06)[0-9]{8,9}$/', Rule::unique('futar', 'telszam')->ignore($this->route('futar'))],
            'jarmu' => ['sometimes', 'required', Rule::in(Futar::JARMUVEK)],
            'hatosugar_km' => ['sometimes', 'required', 'integer', 'min:1'],
            'aktiv' => ['sometimes', 'boolean'],
        ];
    }
}
