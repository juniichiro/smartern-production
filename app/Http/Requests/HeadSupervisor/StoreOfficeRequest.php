<?php

namespace App\Http\Requests\HeadSupervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|unique:offices,name',
            'rules' => 'nullable|array',
            'rules.*.require_rule' => 'required|string',
            'rules.*.rule_value' => 'required|string',
            'rules.*.priority' => 'required|integer|min:1|max:5',
        ];
    }
}
