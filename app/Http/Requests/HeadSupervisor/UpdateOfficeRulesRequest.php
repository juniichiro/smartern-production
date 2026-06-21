<?php

namespace App\Http\Requests\HeadSupervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeRulesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role_id === UserRole::HEAD_SUPERVISOR;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'intern_limit' => 'required|integer|min:1|max:100',
            'rules' => 'array',
            'rules.*.require_rule' => 'required|string|max:100',
            'rules.*.rule_value' => 'required|string|max:500',
            'rules.*.priority' => 'required|integer|min:1|max:5',
        ];
    }
}
