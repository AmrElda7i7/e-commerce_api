<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'image' => $this->isMethod('post') ? 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240':'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ];
    }
}
