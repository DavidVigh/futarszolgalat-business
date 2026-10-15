<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class FutarPozicioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'futar_id' => ['required', 'integer', 'min:1'],
            'gps_x' => ['required', 'numeric', 'between:-90,90'],
            'gps_y' => ['required', 'numeric', 'between:-180,180'],
            'cim' => ['required', 'string', 'max:255'],
        ];
    }
}
