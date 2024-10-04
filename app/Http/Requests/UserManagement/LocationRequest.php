<?php

namespace App\Http\Requests\UserManagement;

use App\Models\UserManagement\Location;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
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
            'status' => ['required', Rule::in(array_keys(Location::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'escape', 'strip_tags'],
            'real_name' => ['trim', 'escape', 'strip_tags'],
            'short_name' => ['trim', 'escape', 'strip_tags'],
            'code_name' => ['trim', 'escape', 'strip_tags'],
            'notes' => ['trim', 'escape', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $location = $this->route()->parameter('location');

        if ($location) {
            return route('locations.edit', $location);
        }

        return route('locations.create');
    }
}
