<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParseResumeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true; // Public form
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'resume_pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:5120'],
        ];
    }
}
