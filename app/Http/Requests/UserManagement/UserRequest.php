<?php

namespace App\Http\Requests\UserManagement;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    use PasswordValidationRules, SanitizesInput;

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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(User::statusList()))],
            'roles' => ['nullable', 'array'],
            'scopes' => ['nullable', 'array'],
        ];

        if ($this->isMethod('post')) {
            $rules = array_merge($rules, [
                'username' => [
                    'required',
                    'string',
                    'regex:/^\S*$/u',
                    'max:255',
                    Rule::unique(User::class),
                ],
                'password' => array_filter($this->passwordRules(), fn ($rule) => $rule != 'confirmed'),
            ]);
        } else {
            $rules = array_merge($rules, [
                'password' => ['nullable', ...array_filter($this->passwordRules(), fn ($rule) => ! in_array($rule, ['required', 'confirmed']))],
            ]);
        }

        return $rules;
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'username' => ['trim', 'strip_tags'],
        ];
    }
}
