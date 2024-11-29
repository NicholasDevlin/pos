<?php

namespace App\Http\Requests\UserManagement;

use App\Models\UserManagement\BusinessUnit;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessUnitRequest extends FormRequest
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
            'real_name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:255'],
            'code_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(BusinessUnit::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'real_name' => ['trim', 'strip_tags'],
            'short_name' => ['trim', 'strip_tags'],
            'code_name' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $businessUnit = $this->route()->parameter('business_unit');

        if ($businessUnit) {
            return route('business-units.edit', $businessUnit);
        }

        return route('business-units.create');
    }
}
