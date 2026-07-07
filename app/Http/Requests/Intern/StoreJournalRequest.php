<?php

namespace App\Http\Requests\Intern;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreJournalRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:100',
            'journal_type' => 'required|in:daily,weekly',
            'entry_date' => 'required|date',
            'notes' => 'nullable|string',
            'included_tasks' => 'nullable',
        ];
    }
}
