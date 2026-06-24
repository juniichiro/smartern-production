<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::ADMIN, UserRole::SUPER_ADMIN]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the user ID from the route for the unique email check
        $userId = $this->route('user')->id;

        return [
            'email' => 'required|email|unique:users,email,'.$userId,
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'office_id' => 'sometimes|required|exists:offices,id',
            'phone_number' => 'sometimes|required|string|max:20',
            'school' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|in:male,female,other',
            'is_hr_head' => 'sometimes|boolean',
            'schedule_assignment' => 'nullable|array',
            'schedule_assignment.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ];
    }
}
