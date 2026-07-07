<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true; // Public form
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => $this->normalizePhone($this->phone_number),
            'emergency_contact_number' => $this->normalizePhone($this->emergency_contact_number),
        ]);
    }

    /**
     * Normalize phone number: strip non-digits and prepend 'zero' if length is ten
     */
    private function normalizePhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) === 10 && ! str_starts_with($digits, '0')) {
            return '0'.$digits;
        }

        return $digits;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'middle_name' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\.]*$/'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'email' => ['required', 'email', 'max:100', 'unique:onboarding_applicants,email'],
            'school' => ['required', 'string', 'max:150', 'regex:/^[a-zA-Z0-9\s\-\.,&()]+$/'],
            'gender' => ['required', 'in:male,female,other'],
            'gender_other_specify' => ['required_if:gender,other', 'nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'phone_number' => ['required', 'string', 'min:10', 'max:11', 'regex:/^[0-9]+$/'],
            'emergency_contact' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'emergency_contact_number' => ['nullable', 'required_with:emergency_contact', 'string', 'min:10', 'max:11', 'regex:/^[0-9]*$/'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'resume_pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:5120'],
            'tech_skills' => ['nullable', 'array'],
            'tech_skills.*' => ['nullable', 'string', 'max:500', 'regex:/^[^<>]*$/'],
            'tech_scores' => ['nullable', 'array'],
            'tech_scores.*' => ['nullable', 'integer', 'min:1', 'max:5'],
            'soft_skills' => ['nullable', 'array'],
            'soft_skills.*' => ['nullable', 'string', 'max:500', 'regex:/^[^<>]*$/'],
            'soft_scores' => ['nullable', 'array'],
            'soft_scores.*' => ['nullable', 'integer', 'min:1', 'max:5'],
            'privacy_acknowledgement' => ['required', 'accepted'],
        ];
    }

    /**
     * Custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An application with this email address has already been submitted. Please use a different email or wait for your evaluation results.',
            'gender_other_specify.required_if' => 'Please specify your gender.',
            'privacy_acknowledgement.accepted' => 'You must acknowledge the Privacy Policy and Data Privacy Act to submit.',
        ];
    }
}
