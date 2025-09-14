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

    public function messages(): array
    {
        return [
            'q.required' => 'Search query is required',
            'q.min' => 'Search query must be at least 2 characters',
            'q.max' => 'Search query cannot exceed 100 characters',
            'country.in' => 'Country must be either sg or mx',
            'per_page.max' => 'Per page cannot exceed 50 items',
            'limit.max' => 'Limit cannot exceed 20 items'
        ];
    }
}
