<?php

namespace App\Http\Requests\Intern;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return $this->user()->role_id === UserRole::INTERN;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $task = $this->route('task');

        return [
            'content' => $task->is_file_required ? 'nullable|string' : 'required|string',
            'submission_file' => $task->is_file_required ? 'required|file|mimes:pdf,zip,png,jpg,jpeg|max:10240' : 'nullable|file|mimes:pdf,zip,png,jpg,jpeg|max:10240',
        ];
    }
}
