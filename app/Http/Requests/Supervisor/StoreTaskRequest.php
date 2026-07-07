<?php

namespace App\Http\Requests\Supervisor;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return Auth::check() && in_array(Auth::user()->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'intern_id' => 'required',
            'due_date' => 'required|date|after_or_equal:now',
            'is_file_required' => 'required|boolean',
        ];
    }
}
