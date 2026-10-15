<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class FutarAllapotRequest extends FormRequest
{
    public function rules(): array
    {
        return ['allapot' => ['required', 'string']];
    }
}
