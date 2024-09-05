<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator ;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|numeric',
            'products' => 'required|array',
            'products.*.id' => 'required|numeric',
            'products.*.quantity' => 'required|numeric|min:1',
            'status' => 'required|in:cash_on_delivery,online_payment',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'products_id.required' => 'At least one product is required.',
            'products_id.array' => 'Products must be an array.',
            'products_id.*.required' => 'Each product must be specified.',
            'products_id.*.numeric' => 'Each product ID must be numeric.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either cash on delivery or online payment.'
        ];
    }
}
