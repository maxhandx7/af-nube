<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
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
            'file' => ['required','file'],
            'expire_days' => ['nullable','integer','min:1','max:30'], // permitir custom expiración (1-30 días)
            'file_password' => 'nullable|min:4'
        ];
    }


    public function messages()
    {
        return [
            'expire_days.min' => 'El período mínimo de expiración es de 1 día.',
            'expire_days.max' => 'El período máximo de expiración es de 30 días.',
            'file_password.min' => 'La contraseña del archivo debe tener al menos 4 caracteres.',
        ];
    }
}
