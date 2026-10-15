<?php

namespace App\Http\Requests\Legacy;

use Illuminate\Foundation\Http\FormRequest;

class RendelesStatuszRequest extends FormRequest
{
    public function rules(): array
    {
        return ['statusz' => ['required', 'string']];
    }
}
