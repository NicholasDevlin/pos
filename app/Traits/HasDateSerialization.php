<?php

namespace App\Traits;

use DateTimeInterface;

trait HasDateSerialization
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H.i.s');
    }
}
