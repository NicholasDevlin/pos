<?php

namespace App\Models\MasterData;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    const STATUS_INACTIVE = '0';

    const STATUS_ACTIVE = '1';

    const TIER_BRONZE = '1';

    const TIER_SILVER = '2';

    const TIER_GOLD = '3';

    protected $fillable = [
        'name',
        'phone',
        'address',
        'tier',
        'notes',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function tierLabel(): string
    {
        return match ($this->tier) {
            self::TIER_BRONZE => '<span class="badge badge-pill badge-dark">Bronze</span>',
            self::TIER_SILVER => '<span class="badge badge-pill badge-soft-secondary">Silver</span>',
            self::TIER_GOLD => '<span class="badge badge-pill badge-warning">Gold</span>',
            default => $this->tier,
        };
    }

    public static function tierList(): array
    {
        return [
            self::TIER_BRONZE => 'Bronze',
            self::TIER_SILVER => 'Silver',
            self::TIER_GOLD => 'Gold',
        ];
    }

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
