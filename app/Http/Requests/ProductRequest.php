<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator ;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0' ,
            'description' => 'required|string' ,
            'quantity' => 'required|numeric|min:1' ,
            'images' => $this->isMethod('post') ? 'required|array' : 'nullable|array',
            'category_id' => 'required|exists:categories,id' ,
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ];
    }

    
}
