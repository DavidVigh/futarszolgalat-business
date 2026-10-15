<?php

namespace App\Http\Requests\Futar;

use Illuminate\Foundation\Http\FormRequest;

class PozicioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gps_x' => ['required', 'numeric', 'between:-90,90'],
            'gps_y' => ['required', 'numeric', 'between:-180,180'],
            'cim' => ['required', 'string', 'max:255'],
        ];
    }
}
