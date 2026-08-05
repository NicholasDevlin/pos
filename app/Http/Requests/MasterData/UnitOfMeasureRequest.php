<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\UnitOfMeasure;
use Elegant\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitOfMeasureRequest extends FormRequest
{
    use SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uom = $this->route()->parameter('units_of_measure');

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('units_of_measure', 'code')->ignore($uom)],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(UnitOfMeasure::statusList()))],
        ];
    }

    public function filters(): array
    {
        return [
            'code' => ['trim', 'strip_tags', 'uppercase'],
            'name' => ['trim', 'strip_tags'],
            'notes' => ['trim', 'strip_tags'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        $uom = $this->route()->parameter('units_of_measure');

        if ($uom) {
            return route('units-of-measure.edit', $uom);
        }

        return route('units-of-measure.create');
    }
}
