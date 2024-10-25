<?php

namespace App\Http\Requests;

use App\Models\Option;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OptionRequest extends FormRequest
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
            'value' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Option::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'value' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $option = $this->route()->parameter('option');

        if ($option) {
            return route('options.edit', $option);
        }

        return route('options.create');
    }
}
