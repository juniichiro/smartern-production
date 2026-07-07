<?php

namespace App\Http\Requests\Supervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:completed,revision,pending',
            'remarks' => 'required_if:status,revision|nullable|string',
            'new_due_date' => 'nullable|date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules
     */
    public function messages(): array
    {
        return [
            'remarks.required_if' => 'Please provide remarks to guide the intern through the revision.',
        ];
    }
}
