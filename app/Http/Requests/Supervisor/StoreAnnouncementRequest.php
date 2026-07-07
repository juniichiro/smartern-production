<?php

namespace App\Http\Requests\Supervisor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR, UserRole::SUPERVISOR]);
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'scope' => 'nullable|string|in:global,office',
        ];
    }

    /**
     * Sanitize input content to block stored XSS vectors
     */
    protected function passedValidation()
    {
        $content = $this->input('content');

        if ($content) {
            $config = \HTMLPurifier_Config::createDefault();
            
            // Set Cache Path to a writable Laravel storage folder
            $cachePath = storage_path('app/htmlpurifier');
            if (!file_exists($cachePath)) {
                mkdir($cachePath, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cachePath);

            $config->set('HTML.Allowed', 'p[style],b[style],strong[style],i[style],em[style],u[style],a[href|target|title|rel|style],ul[style],ol[style],li[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],br,span[style],blockquote[style],pre[style],code[style]');
            $config->set('CSS.AllowedProperties', 'font-weight,font-style,text-decoration,color,background-color,font-family,font-size,text-align,list-style-type');
            $config->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);

            $purifier = new \HTMLPurifier($config);
            $content = $purifier->purify($content);
        }

        $this->merge([
            'content' => $content,
        ]);
    }
}
