<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataInitiationImport implements SkipsOnError, ToModel, WithHeadingRow
{
    use Importable, SkipsErrors;

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
