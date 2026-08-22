<?php

namespace App\Models\MasterData;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    const STATUS_INACTIVE = '0';

    const STATUS_ACTIVE = '1';

    const CODE_LENGTH = '4';

    protected $fillable = [
        'product_category_id',
        'code',
        'name',
        'notes',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $prefixLength = strlen($model->code) + 1; // Code prefix length

            $code = $model->newQuery()
                ->where('product_category_id', $model->product_category_id)
                ->select(DB::raw("SUBSTRING(code, $prefixLength) AS code"))
                ->latest('code')
                ->value('code') + 1;

            $model->code = $model->code.str_pad($code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productUoms(): HasMany
    {
        return $this->hasMany(ProductUom::class);
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
