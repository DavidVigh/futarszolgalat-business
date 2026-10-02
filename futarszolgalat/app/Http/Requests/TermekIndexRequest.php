<?php

namespace App\Http\Requests;

use App\Models\Termek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TermekIndexRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Allow both ?tipus=etel and ?tipus[]=etel&tipus[]=ital
        foreach (['tipus', 'fogas', 'allergen_nelkul'] as $mezo) {
            if ($this->filled($mezo) && ! is_array($this->input($mezo))) {
                $this->merge([$mezo => [$this->input($mezo)]]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'tipus' => ['sometimes', 'array'],
            'tipus.*' => [Rule::in(Termek::TIPUSOK)],
            'fogas' => ['sometimes', 'array'],
            'fogas.*' => [Rule::in(Termek::FOGASOK)],
            'allergen_nelkul' => ['sometimes', 'array'],
            'allergen_nelkul.*' => ['integer', 'exists:allergen,kod'],
            'kereses' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
