<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\ProductCategory;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
{
    use SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(ProductCategory::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'code' => ['trim', 'strip_tags', 'upper_case'],
            'name' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $productCategory = $this->route()->parameter('product_category');

        if ($productCategory) {
            return route('product-categories.edit', $productCategory);
        }

        return route('product-categories.create');
    }
}
