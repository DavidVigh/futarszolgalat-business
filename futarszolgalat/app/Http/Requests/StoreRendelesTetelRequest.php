<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRendelesTetelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'termek_id' => ['required', 'integer', Rule::exists('termek', 'id')->where('aktiv', 1)],
            'db' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
