<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255|unique:products,title',
            'price'       => 'required|decimal:0,2|between:0,999999.99',
            'currency_id' => 'required|int|exists:currencies,id',
        ];
    }
}
