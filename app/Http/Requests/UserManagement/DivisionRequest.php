<?php

namespace App\Http\Requests\UserManagement;

use App\Models\UserManagement\Division;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DivisionRequest extends FormRequest
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
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Division::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'escape', 'strip_tags'],
            'notes' => ['trim', 'escape', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $division = $this->route()->parameter('division');

        if ($division) {
            return route('divisions.edit', $division);
        }

        return route('divisions.create');
    }
}
