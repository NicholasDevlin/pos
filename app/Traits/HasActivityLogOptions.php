<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;

trait HasActivityLogOptions
{
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
