<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRendelesTetelRequest extends FormRequest
{
    public function rules(): array
    {
        return ['db' => ['required', 'integer', 'min:1']];
    }
}
