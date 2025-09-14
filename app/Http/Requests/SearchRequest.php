<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'country' => ['sometimes', 'string', 'in:sg,mx'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20']
        ];
    }
}
