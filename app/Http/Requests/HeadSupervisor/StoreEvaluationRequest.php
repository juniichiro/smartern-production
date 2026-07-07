<?php

namespace App\Http\Requests\HeadSupervisor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role->name === 'head_supervisor';
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'interview_notes' => 'nullable|string',
            'verified_skills' => 'required|array',
            'behavioral_score' => 'required|integer|min:1|max:5',
            'is_recommended' => 'required|boolean',
            'resume_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }
}
