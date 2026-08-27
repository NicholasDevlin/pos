<?php

namespace App\Http\Requests\Transaction;

use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;

class CompanyProfileRequest extends FormRequest
{
    use SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value.company_name' => ['required', 'string', 'max:255'],
            'value.address' => ['required', 'string'],
            'value.phone_number' => ['required', 'string', 'max:255'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('sales.edit-company-profile');
    }
}
