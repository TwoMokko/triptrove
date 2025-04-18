<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Или ваша логика авторизации
    }

    public function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:heic,jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'folder' => 'sometimes|string|alpha_dash|max:50',
            'subfolder' => 'nullable|string|alpha_dash|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'photo.max' => 'File size should not exceed 5MB',
            'folder.alpha_dash' => 'Folder name can only contain letters, numbers, dashes and underscores'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'file_info' => [
                'size' => $this->file('photo')?->getSize(),
                'mime' => $this->file('photo')?->getMimeType()
            ]
        ], 422));
    }
}
