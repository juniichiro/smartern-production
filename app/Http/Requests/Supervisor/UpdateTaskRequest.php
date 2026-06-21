<?php

namespace App\Http\Requests\Supervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'is_file_required' => 'required|boolean',
        ];
    }
}
