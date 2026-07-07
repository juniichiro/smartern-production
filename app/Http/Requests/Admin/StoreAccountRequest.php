<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::ADMIN, UserRole::SUPER_ADMIN]);
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id|not_in:' . UserRole::SUPER_ADMIN->value,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'is_hr_head' => 'sometimes|boolean',
        ];

        $role = Role::find($this->role_id);

        if ($role) {
            $roleName = $role->name;

            // INTERN SPECIFIC
            if ($roleName === 'intern') {
                $rules['gender'] = 'required|in:male,female,other';
                $rules['school'] = 'required|string|max:100';
                $rules['office_id'] = 'required|exists:offices,id';
                $rules['emergency_contact'] = 'nullable|string|max:100';
                $rules['schedule_assignment'] = 'nullable|array';
                $rules['schedule_assignment.*'] = 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday';
            }

            // SUPERVISOR SPECIFIC
            if ($roleName === 'supervisor') {
                $rules['office_id'] = 'required|exists:offices,id';
            }

            // SCHOOL COORDINATOR SPECIFIC
            if ($roleName === 'school_coordinator') {
                $rules['school'] = 'required|string|max:100';
            }
        }

        return $rules;
    }
}
