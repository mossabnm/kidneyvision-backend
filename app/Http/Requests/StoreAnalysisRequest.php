<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnalysisRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'image.required' => 'A kidney scan image is required.',
            'image.image' => 'The file must be a valid image.',
            'image.mimes' => 'Only JPEG, PNG, and JPG images are supported.',
            'image.max' => 'Image size must not exceed 10MB.',
        ];
    }
}
