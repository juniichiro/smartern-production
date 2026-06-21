<?php

namespace App\Http\Requests\Supervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR, UserRole::SUPERVISOR]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    /**
     * Sanitize input content to block stored XSS vectors.
     */
    protected function passedValidation()
    {
        $content = $this->input('content');

        // Remove script tags and their content
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);

        // Remove iframe tags
        $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);

        // Remove inline event handlers (like onload, onerror, onclick, etc.)
        $content = preg_replace('/on\w+\s*=\s*["\'].*?["\']/is', '', $content);
        $content = preg_replace('/on\w+\s*=\s*[^\s>]+/is', '', $content);

        // Remove javascript: pseudo-protocol links
        $content = preg_replace('/href\s*=\s*["\']\s*javascript:.*?["\']/is', 'href="#"', $content);

        $this->merge([
            'content' => $content,
        ]);
    }
}
