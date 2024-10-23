<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataInitiationExport implements FromArray, WithHeadings
{
    public string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function array(): array
    {
        return [[]];
    }

    public function headings(): array
    {
        return (new $this->model)->getFillable();
    }
}
