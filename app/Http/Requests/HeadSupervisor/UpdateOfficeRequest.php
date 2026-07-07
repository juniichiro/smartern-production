<?php

namespace App\Http\Requests\HeadSupervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return $this->user()->role_id === UserRole::HEAD_SUPERVISOR;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $officeId = $this->route('office')->id;

        return [
            'name' => "required|string|max:255|unique:offices,name,{$officeId}",
        ];
    }
}
