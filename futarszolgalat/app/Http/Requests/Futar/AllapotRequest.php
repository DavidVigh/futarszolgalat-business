<?php

namespace App\Http\Requests\Futar;

use App\Models\Futar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllapotRequest extends FormRequest
{
    public function rules(): array
    {
        return ['allapot' => ['required', Rule::in([Futar::ALLAPOT_ELERHETO, Futar::ALLAPOT_SZUNET])]];
    }
}
