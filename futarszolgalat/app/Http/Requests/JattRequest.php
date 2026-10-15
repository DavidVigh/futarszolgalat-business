<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JattRequest extends FormRequest
{
    public function rules(): array
    {
        return ['jatt' => ['required', 'integer', 'min:1']];
    }
}
