<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErtekelesRequest extends FormRequest
{
    public function rules(): array
    {
        return ['ertekeles' => ['required', 'integer', 'between:1,5']];
    }
}
