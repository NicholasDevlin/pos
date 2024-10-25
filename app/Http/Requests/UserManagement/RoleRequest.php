<?php

namespace App\Http\Requests\UserManagement;

use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    use SanitizesInput;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! ($this->has('permissions') && in_array('logs.show.scope', $this->input('permissions')))) {
            $this->merge(['log_scopes' => null]);
        }
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
            'permissions' => ['nullable', 'array'],
            'log_scopes' => [Rule::when($this->has('permissions') && in_array('logs.show.scope', $this->input('permissions')), ['required', 'array'], ['nullable'])],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }
}
