<?php

namespace App\Models\Transaction;

use App\Models\MasterData\Customer;
use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    const STATUS_NEW = '0';

    const STATUS_DELIVERED = '1';

    const STATUS_UNPAID = '2';

    const STATUS_PARTIALLY_PAID = '3';

    const STATUS_PAID = '4';

    const SERIES_NUMBER_LENGTH = '4';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_tier',
        'series_number',
        'discount_percentage',
        'discount_nominal',
        'tax_percentage',
        'grand_total',
        'notes',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $currentMonth = date('m');
            $currentYear = date('Y');

            do {
                $seriesNumber = random_int(1, (10 ** self::SERIES_NUMBER_LENGTH) - 1);
                $formatted = str_pad($seriesNumber, self::SERIES_NUMBER_LENGTH, '0', STR_PAD_LEFT)."/SLS/$currentMonth/$currentYear";
            } while (
                $model->newQuery()->where('series_number', $formatted)->exists()
            );

            $model->series_number = $formatted;
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => '<span class="badge badge-pill badge-soft-primary">Pesanan Baru</span>',
            self::STATUS_DELIVERED => '<span class="badge badge-pill badge-warning">Dalam Pengantaran</span>',
            self::STATUS_UNPAID => '<span class="badge badge-pill badge-secondary">Belum Dibayar</span>',
            self::STATUS_PARTIALLY_PAID => '<span class="badge badge-pill badge-soft-warning">Dibayar Setengah</span>',
            self::STATUS_PAID => '<span class="badge badge-pill badge-success">Lunas</span>',
            default => $this->status,
        };
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_NEW => 'Pesanan Baru',
            self::STATUS_DELIVERED => 'Dalam Pengantaran',
            self::STATUS_UNPAID => 'Belum Dibayar',
            self::STATUS_PARTIALLY_PAID => 'Dibayar Setengah',
            self::STATUS_PAID => 'Lunas',
        ];
    }
}
