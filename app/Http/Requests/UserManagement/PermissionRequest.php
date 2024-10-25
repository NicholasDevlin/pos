<?php

namespace App\Http\Requests\UserManagement;

use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    use SanitizesInput;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string'],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $permission = $this->route()->parameter('permission');

        if ($permission) {
            return route('permissions.edit', $permission);
        }

        return route('permissions.create');
    }
}
