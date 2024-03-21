<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'string',
                'between:1,255',
                Rule::unique('products', 'title')->ignore($this->product),
            ],
            'price'       => 'decimal:0,2|between:0,999999.99',
            'currency_id' => 'int|exists:currencies,id',
        ];
    }
}
