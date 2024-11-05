<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataInitiationImport implements ToModel, WithHeadingRow
{
    use Importable;

    public string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function model(array $row)
    {
        return new $this->model($row);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
