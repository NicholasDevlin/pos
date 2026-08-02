<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\Customer;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    use SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'address' => ['required', 'string', 'max:255'],
            'tier' => ['required', Rule::in(array_keys(Customer::tierList()))],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Customer::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'name' => ['trim', 'strip_tags'],
            'phone' => ['trim', 'strip_tags'],
            'address' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $customer = $this->route()->parameter('customer');

        if ($customer) {
            return route('customers.edit', $customer);
        }

        return route('customers.create');
    }
}
