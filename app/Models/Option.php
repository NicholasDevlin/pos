<?php

namespace App\Models;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Option extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    const STATUS_INACTIVE = '0';

    const STATUS_ACTIVE = '1';

    protected $fillable = [
        'name',
        'value',
        'notes',
        'status',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_INACTIVE => '<span class="badge badge-pill badge-soft-secondary">Tidak Aktif</span>',
            self::STATUS_ACTIVE => '<span class="badge badge-pill badge-success">Aktif</span>',
            default => $this->status,
        };
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_ACTIVE => 'Aktif',
        ];
    }
}
